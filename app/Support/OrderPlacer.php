<?php

namespace App\Support;

use App\Models\Agent;
use App\Models\AgentSale;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\OrderTrail;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\SiteInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns cart lines into an order — used by the storefront checkout and the
 * admin's manual order form, so both get the same rules:
 *
 *  - prices come from the catalogue, never from the request;
 *  - stock is checked and taken with conditional UPDATEs inside one
 *    transaction, so two shoppers can't both buy the last item;
 *  - a coupon's use is claimed the same way, so it can't go over its limit;
 *  - any failure rolls the whole order back and throws OrderException.
 */
class OrderPlacer
{
    /**
     * @param  array<int, array{product_id: int, variation_id?: ?int, qty: int}>  $lines
     * @param  array{name: string, phone: string, email?: ?string}  $customer
     * @param  array{address_line_1: string, address_line_2?: ?string, city: string, state?: ?string, postal_code?: ?string, country: string}  $address
     * @param  array{user_id?: ?int, coupon?: ?Coupon, agent?: ?Agent, delivery_charges?: ?float, admin_discount_type?: ?string, admin_discount_value?: ?float, status?: string, payment_method?: string, remarks?: ?string, placed_by?: string}  $options
     *
     * @throws OrderException
     */
    public function place(array $lines, array $customer, array $address, array $options = []): Order
    {
        $lines = $this->mergeLines($lines);

        if (empty($lines)) {
            throw new OrderException('Your bag is empty.', 'items');
        }

        $status = $options['status'] ?? 'Pending';
        $placedBy = $options['placed_by'] ?? 'Customer';

        return DB::transaction(function () use ($lines, $customer, $address, $options, $status, $placedBy) {
            $items = $this->priceLines($lines);
            $subtotal = round(array_sum(array_column($items, 'line_total')), 2);

            $coupon = $options['coupon'] ?? null;
            $discount = 0.0;
            if ($coupon) {
                $coupon->refresh();
                if (!$coupon->isValid()) {
                    throw new OrderException('That discount code is no longer valid.', 'coupon_code');
                }
                if (!$coupon->meetsMinimum($subtotal)) {
                    throw new OrderException('This discount code needs a minimum order of ' . money($coupon->min_order_amount) . '.', 'coupon_code');
                }
                if (!$coupon->claimUse()) {
                    throw new OrderException('That discount code has reached its usage limit.', 'coupon_code');
                }
                $discount = $coupon->discountFor($subtotal);
            }

            // Admin's own discount, applied after the coupon to whatever is
            // left, so the two together never exceed the subtotal.
            $adminDiscountValue = round((float) ($options['admin_discount_value'] ?? 0), 2);
            $adminDiscountType = $options['admin_discount_type'] ?? 'fixed';
            $adminDiscount = 0.0;
            if ($adminDiscountValue > 0) {
                $remaining = $subtotal - $discount;
                $adminDiscount = $adminDiscountType === 'percent'
                    ? $remaining * ($adminDiscountValue / 100)
                    : $adminDiscountValue;
                $adminDiscount = round(min($adminDiscount, $remaining), 2);
                $discount = round($discount + $adminDiscount, 2);
            }

            $deliveryCharges = array_key_exists('delivery_charges', $options) && $options['delivery_charges'] !== null
                ? round((float) $options['delivery_charges'], 2)
                : SiteInfo::deliveryCharge($subtotal - $discount);

            /** @var Agent|null $agent */
            $agent = $options['agent'] ?? null;

            $order = new Order([
                'user_id' => $options['user_id'] ?? null,
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'customer_email' => $customer['email'] ?? null,
                'agent_id' => $agent?->id,
                'coupon_code' => $coupon?->code,
                'discount_amount' => $discount,
                'admin_discount' => $adminDiscount,
                'admin_discount_type' => $adminDiscount > 0 ? $adminDiscountType : null,
                'admin_discount_value' => $adminDiscount > 0 ? $adminDiscountValue : null,
                'total_amount' => round(max(0, $subtotal - $discount) + $deliveryCharges, 2),
                'delivery_charges' => $deliveryCharges,
                'status' => $status,
                'payment_method' => $options['payment_method'] ?? config('store.payment_methods.cod'),
                'cart' => array_map(fn (array $item) => [
                    'product_id' => $item['product']->id,
                    'variation_id' => $item['variant']?->id,
                    'name' => $item['product']->name,
                    'variant' => $item['variant']?->label,
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                ], $items),
                'remarks' => $options['remarks'] ?? null,
            ]);
            $order->uid = $this->newUid();
            $order->save();

            foreach ($items as $item) {
                $this->takeStock($item);

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $item['product']->id,
                    'variation_id' => $item['variant']?->id,
                    'product_name' => $item['product']->name,
                    'variant_label' => $item['variant']?->label,
                    'sku' => $item['variant']?->sku,
                    'unit_price' => $item['unit_price'],
                    'qty' => $item['qty'],
                    'item_total' => $item['line_total'],
                    'discount' => $item['compare_at'] !== null
                        ? round(($item['compare_at'] - $item['unit_price']) * $item['qty'], 2)
                        : 0,
                    'status' => $status,
                ]);

                if ($agent) {
                    AgentSale::create([
                        'agent_id' => $agent->id,
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->name . ($item['variant'] ? ' — ' . $item['variant']->label : ''),
                        'quantity' => $item['qty'],
                        'unit_price' => $item['unit_price'],
                        'total_amount' => $item['line_total'],
                        'commission_rate' => $agent->commission_rate,
                        'commission_amount' => round($item['line_total'] * ((float) $agent->commission_rate / 100), 2),
                        'sale_date' => now()->toDateString(),
                        'notes' => 'Auto-logged from order ' . $order->uid,
                    ]);
                }
            }

            $delivery = new OrderDelivery([
                'order_id' => $order->id,
                'full_address' => trim($address['address_line_1'] . ' ' . ($address['address_line_2'] ?? '')),
                'country' => $address['country'],
                'postal_code' => $address['postal_code'] ?? null,
                'state' => $address['state'] ?? null,
                'city' => $address['city'],
            ]);
            $delivery->address_line_1 = $address['address_line_1'];
            $delivery->address_line_2 = $address['address_line_2'] ?? null;
            $delivery->save();

            OrderTrail::create([
                'order_id' => $order->id,
                'status' => $status,
                'updated_by' => $placedBy,
            ]);

            return $order;
        });
    }

    /** Same product/variant on several lines → one line. */
    private function mergeLines(array $lines): array
    {
        $merged = [];

        foreach ($lines as $line) {
            $qty = (int) ($line['qty'] ?? 0);
            if ($qty < 1) {
                continue;
            }

            $variationId = (int) ($line['variation_id'] ?? 0);
            $key = Cart::key((int) $line['product_id'], $variationId ?: null);
            $merged[$key] = ($merged[$key] ?? 0) + $qty;
        }

        return $merged;
    }

    /** @return array<int, array{product: Product, variant: ?ProductVariation, qty: int, unit_price: float, compare_at: ?float, line_total: float}> */
    private function priceLines(array $lines): array
    {
        $keys = array_map([Cart::class, 'parseKey'], array_keys($lines));
        $products = Product::isActive()
            ->with('variations')
            ->whereIn('id', array_unique(array_column($keys, 0)))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $items = [];
        foreach ($lines as $key => $qty) {
            [$productId, $variationId] = Cart::parseKey($key);
            $product = $products->get($productId);

            if (!$product) {
                throw new OrderException('One of the items in your bag is no longer available.', 'items');
            }

            $variant = null;
            if ($product->isVariable()) {
                $variant = $variationId
                    ? ProductVariation::where('product_id', $product->id)->whereKey($variationId)->lockForUpdate()->first()
                    : null;

                if (!$variant) {
                    throw new OrderException("Please choose a size/colour for {$product->name}.", 'items');
                }
            }

            $available = $variant ? (int) $variant->quantity : $product->qty;
            if ($available !== null && $qty > $available) {
                $name = $product->name . ($variant ? ' (' . $variant->label . ')' : '');
                throw new OrderException($available > 0
                    ? "Only $available left of $name — please lower the quantity."
                    : "$name has just sold out.", 'items');
            }

            $unitPrice = $variant ? $variant->unitPrice($product) : (float) $product->current_price;

            $items[] = [
                'product' => $product,
                'variant' => $variant,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'compare_at' => $variant ? $variant->compareAtPrice($product) : ($product->is_on_sale ? (float) $product->price : null),
                'line_total' => round($unitPrice * $qty, 2),
            ];
        }

        return $items;
    }

    private function takeStock(array $item): void
    {
        $qty = $item['qty'];

        if ($item['variant']) {
            $taken = ProductVariation::whereKey($item['variant']->id)->where('quantity', '>=', $qty)->decrement('quantity', $qty);
            if ($taken !== 1) {
                throw new OrderException("{$item['product']->name} ({$item['variant']->label}) has just sold out.", 'items');
            }
            $item['product']->syncStockFromVariants();

            return;
        }

        if ($item['product']->qty === null) {
            return;
        }

        $taken = Product::whereKey($item['product']->id)->where('qty', '>=', $qty)->decrement('qty', $qty);
        if ($taken !== 1) {
            throw new OrderException("{$item['product']->name} has just sold out.", 'items');
        }
    }

    private function newUid(): string
    {
        do {
            $uid = config('store.order_prefix') . '-' . strtoupper(Str::random(8));
        } while (Order::where('uid', $uid)->exists());

        return $uid;
    }
}
