<?php

namespace App\Support;

use App\Models\SiteInfo;
use Illuminate\Database\QueryException;

/**
 * Store-wide settings (the single site_infos row) read once per request.
 * Cached on the request rather than statically so a settings change is
 * picked up on the very next request — including within a test.
 */
class Store
{
    public static function info(): ?SiteInfo
    {
        $attributes = request()->attributes;

        if (!$attributes->has('store.info')) {
            try {
                $info = SiteInfo::first();
            } catch (QueryException) {
                $info = null;
            }
            $attributes->set('store.info', $info);
        }

        return $attributes->get('store.info');
    }

    /** @return array{name: string, tagline: ?string} */
    public static function brand(): array
    {
        $info = self::info();

        return [
            'name' => $info?->brand_name ?: config('store.brand.name'),
            'tagline' => $info?->tagline ?: config('store.brand.tagline'),
        ];
    }

    public static function currency(): string
    {
        return self::info()?->currency ?: config('store.currency');
    }

    public static function money(float|int|string|null $amount): string
    {
        $amount = round((float) $amount, 2);
        $decimals = floor($amount) == $amount ? 0 : 2;

        return self::currency() . ' ' . number_format($amount, $decimals);
    }

    public static function freeShippingThreshold(): ?float
    {
        $threshold = self::info()?->free_shipping_threshold;

        return $threshold !== null && (float) $threshold > 0 ? (float) $threshold : null;
    }
}
