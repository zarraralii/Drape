<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderTrail;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;

/**
 * Status changes that move stock: cancelling an order puts its items back
 * on the shelf, and un-cancelling it takes them again (if they're still
 * there). orders.restocked_at records which state the stock is in, so
 * flipping the status back and forth never double-counts.
 */
class Inventory
{
    /** @throws OrderException */
    public static function changeStatus(Order $order, string $status, string $updatedBy = 'Admin'): void
    {
        DB::transaction(function () use ($order, $status, $updatedBy) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);

            if ($status === 'Cancelled' && !$order->restocked_at) {
                self::release($order);
                $order->restocked_at = now();
            } elseif ($status !== 'Cancelled' && $order->restocked_at) {
                self::reserve($order);
                $order->restocked_at = null;
            }

            $order->status = $status;
            $order->save();
            $order->items()->update(['status' => $status]);

            OrderTrail::create(['order_id' => $order->id, 'status' => $status, 'updated_by' => $updatedBy]);
        });
    }

    private static function release(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->variation_id && ($variant = ProductVariation::find($item->variation_id))) {
                $variant->increment('quantity', (int) $item->qty);
                $variant->product?->syncStockFromVariants();
            } elseif (!$item->variation_id) {
                Product::withTrashed()->whereKey($item->item_id)->whereNotNull('qty')->increment('qty', (int) $item->qty);
            }
        }
    }

    private static function reserve(Order $order): void
    {
        foreach ($order->items as $item) {
            $qty = (int) $item->qty;

            if ($item->variation_id) {
                $taken = ProductVariation::whereKey($item->variation_id)->where('quantity', '>=', $qty)->decrement('quantity', $qty);
                if ($taken !== 1) {
                    throw new OrderException("Not enough stock left of {$item->display_name} ({$item->variant_label}) to reopen this order.", 'status');
                }
                ProductVariation::find($item->variation_id)?->product?->syncStockFromVariants();
            } else {
                $product = Product::withTrashed()->find($item->item_id);
                if ($product && $product->qty !== null
                    && Product::whereKey($product->id)->where('qty', '>=', $qty)->decrement('qty', $qty) !== 1) {
                    throw new OrderException("Not enough stock left of {$item->display_name} to reopen this order.", 'status');
                }
            }
        }
    }
}
