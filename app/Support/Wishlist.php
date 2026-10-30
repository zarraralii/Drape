<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Session-backed wishlist — no DB table, no login required, mirrors
 * App\Support\Cart.
 */
class Wishlist
{
    protected const SESSION_KEY = 'wishlist';

    public static function raw(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public static function has(int $productId): bool
    {
        return in_array($productId, self::raw(), true);
    }

    public static function toggle(int $productId): bool
    {
        $wishlist = self::raw();

        if (in_array($productId, $wishlist, true)) {
            $wishlist = array_values(array_diff($wishlist, [$productId]));
            Session::put(self::SESSION_KEY, $wishlist);

            return false;
        }

        $wishlist[] = $productId;
        Session::put(self::SESSION_KEY, $wishlist);

        return true;
    }

    public static function remove(int $productId): void
    {
        $wishlist = array_values(array_diff(self::raw(), [$productId]));
        Session::put(self::SESSION_KEY, $wishlist);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public static function count(): int
    {
        return count(self::raw());
    }

    /**
     * @return Collection<int, Product>
     */
    public static function items(): Collection
    {
        $ids = self::raw();

        if (empty($ids)) {
            return collect();
        }

        return Product::isActive()->whereIn('id', $ids)->with(['category', 'variations'])->get();
    }
}
