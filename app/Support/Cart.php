<?php

namespace App\Support;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Session-backed cart — no DB table, no login required. Each line is keyed
 * "{productId}" for a simple product or "{productId}:{variationId}" for
 * one size/colour of a variable product. Prices and stock are always
 * re-read from the catalogue, never trusted from the session.
 */
class Cart
{
    protected const SESSION_KEY = 'cart';
    protected const COUPON_SESSION_KEY = 'cart_coupon_code';

    public static function raw(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public static function key(int $productId, ?int $variationId = null): string
    {
        return $variationId ? "$productId:$variationId" : (string) $productId;
    }

    /** @return array{0: int, 1: ?int} */
    public static function parseKey(string|int $key): array
    {
        [$productId, $variationId] = array_pad(explode(':', (string) $key, 2), 2, null);

        return [(int) $productId, $variationId !== null ? (int) $variationId : null];
    }

    public static function quantityOf(string $key): int
    {
        return (int) (self::raw()[$key] ?? 0);
    }

    /** Adds $qty, capped at $max when stock is limited. Returns the new line quantity. */
    public static function add(string $key, int $qty, ?int $max = null): int
    {
        $cart = self::raw();
        $newQty = ($cart[$key] ?? 0) + max(1, $qty);

        if ($max !== null) {
            $newQty = min($newQty, max(0, $max));
        }

        if ($newQty > 0) {
            $cart[$key] = $newQty;
        }

        Session::put(self::SESSION_KEY, $cart);

        return $newQty;
    }

    public static function set(string $key, int $qty, ?int $max = null): void
    {
        $cart = self::raw();

        if ($max !== null) {
            $qty = min($qty, max(0, $max));
        }

        if ($qty <= 0) {
            unset($cart[$key]);
        } else {
            $cart[$key] = $qty;
        }

        Session::put(self::SESSION_KEY, $cart);
    }

    public static function remove(string $key): void
    {
        $cart = self::raw();
        unset($cart[$key]);
        Session::put(self::SESSION_KEY, $cart);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
        self::removeCoupon();
    }

    public static function count(): int
    {
        return (int) array_sum(self::raw());
    }

    /**
     * @return Collection<int, array{key: string, product: Product, variant: ?ProductVariation, label: ?string, qty: int, max: ?int, unit_price: float, compare_at: ?float, line_total: float, image: ?string}>
     */
    public static function items(): Collection
    {
        $cart = self::raw();

        if (empty($cart)) {
            return collect();
        }

        $parsed = collect(array_keys($cart))->mapWithKeys(fn ($key) => [(string) $key => self::parseKey($key)]);
        $products = Product::isActive()->with('variations')->whereIn('id', $parsed->pluck(0)->unique())->get()->keyBy('id');

        return collect($cart)
            ->map(function ($qty, $key) use ($parsed, $products) {
                [$productId, $variationId] = $parsed[(string) $key];
                $product = $products->get($productId);

                if (!$product) {
                    return null;
                }

                $variant = $variationId ? $product->variations->firstWhere('id', $variationId) : null;

                // A variable product can only be bought as one of its variants.
                if ($product->isVariable() !== (bool) $variant) {
                    return null;
                }

                $unitPrice = $variant ? $variant->unitPrice($product) : (float) $product->current_price;
                $compareAt = $variant ? $variant->compareAtPrice($product) : ($product->is_on_sale ? (float) $product->price : null);
                $max = $variant ? (int) $variant->quantity : ($product->qty === null ? null : (int) $product->qty);

                return [
                    'key' => (string) $key,
                    'product' => $product,
                    'variant' => $variant,
                    'label' => $variant?->label,
                    'qty' => (int) $qty,
                    'max' => $max,
                    'unit_price' => $unitPrice,
                    'compare_at' => $compareAt,
                    'line_total' => round($unitPrice * $qty, 2),
                    'image' => $variant?->image ?: ($product->cover_image_thumbnail ?: $product->cover_image),
                ];
            })
            ->filter()
            ->values();
    }

    /** Lines in the shape App\Support\OrderPlacer takes. */
    public static function orderLines(): array
    {
        return self::items()->map(fn (array $item) => [
            'product_id' => $item['product']->id,
            'variation_id' => $item['variant']?->id,
            'qty' => $item['qty'],
        ])->all();
    }

    public static function subtotal(): float
    {
        return round((float) self::items()->sum('line_total'), 2);
    }

    /**
     * The currently-applied coupon, or null if none is applied or the one
     * in session is no longer valid (expired/deactivated/exhausted since
     * it was applied — checked fresh on every read, never trusted stale).
     */
    public static function appliedCoupon(): ?Coupon
    {
        $code = Session::get(self::COUPON_SESSION_KEY);

        if (!$code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon || !$coupon->isValid()) {
            Session::forget(self::COUPON_SESSION_KEY);

            return null;
        }

        return $coupon;
    }

    /**
     * @return array{success: bool, message: string}
     */
    public static function applyCoupon(string $code): array
    {
        $code = strtoupper(trim($code));

        if ($code === '') {
            return ['success' => false, 'message' => 'Enter a discount code.'];
        }

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon || !$coupon->isValid()) {
            return ['success' => false, 'message' => 'That discount code isn\'t valid.'];
        }

        $subtotal = self::subtotal();

        if ($coupon->min_order_amount !== null && $subtotal < (float) $coupon->min_order_amount) {
            return [
                'success' => false,
                'message' => 'This code needs a minimum order of ' . money($coupon->min_order_amount) . '.',
            ];
        }

        Session::put(self::COUPON_SESSION_KEY, $coupon->code);

        return ['success' => true, 'message' => 'Discount "' . $coupon->code . '" applied.'];
    }

    /** The code the shopper applied, even if it has since stopped being valid. */
    public static function couponCode(): ?string
    {
        return Session::get(self::COUPON_SESSION_KEY);
    }

    public static function removeCoupon(): void
    {
        Session::forget(self::COUPON_SESSION_KEY);
    }

    public static function discount(?float $subtotal = null): float
    {
        $coupon = self::appliedCoupon();

        return $coupon ? $coupon->discountFor($subtotal ?? self::subtotal()) : 0.0;
    }

    /**
     * Everything a summary needs in one pass (items are only loaded once).
     *
     * @return array{items: Collection, count: int, subtotal: float, discount: float, coupon: ?Coupon, delivery: float, total: float, free_shipping_threshold: ?float, free_shipping_remaining: ?float}
     */
    public static function summary(): array
    {
        $items = self::items();
        $subtotal = round((float) $items->sum('line_total'), 2);
        $coupon = self::appliedCoupon();
        $discount = $coupon ? $coupon->discountFor($subtotal) : 0.0;
        $delivery = $items->isEmpty() ? 0.0 : \App\Models\SiteInfo::deliveryCharge($subtotal - $discount);
        $threshold = Store::freeShippingThreshold();

        return [
            'items' => $items,
            'count' => (int) $items->sum('qty'),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'coupon' => $coupon,
            'delivery' => $delivery,
            'total' => round(max(0, $subtotal - $discount) + $delivery, 2),
            'free_shipping_threshold' => $threshold,
            'free_shipping_remaining' => $threshold !== null ? max(0, round($threshold - ($subtotal - $discount), 2)) : null,
        ];
    }
}
