<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteInfo extends Model
{
    protected $fillable = [
        'brand_name', 'tagline', 'announcement_text', 'currency', 'hero_description', 'hero_eyebrow', 'hero_subheadline', 'hero_cta_label',
        'story_eyebrow', 'story_headline', 'story_description', 'story_cta_label', 'story_cta_link',
        'privacy_policy', 'tnc', 'about', 'email', 'phone', 'location', 'lat', 'lng', 'service_timing',
        'facebook_link', 'instagram_link', 'tiktok_link', 'youtube_link', 'free_shipping_quote', 'gallery_quote',
        'promo_banner_left_link', 'promo_banner_full_link', 'promo_banner_right_link',
        'promo_banner_left_label', 'promo_banner_full_label', 'promo_banner_right_label',
        'delivery_charges', 'free_shipping_threshold', 'size_guide', 'shipping_policy', 'return_and_exchange',
        'meta_title', 'meta_description', 'google_site_verification',
    ];

    /**
     * The flat delivery fee for an order of $subtotal — free once the
     * subtotal reaches the free-shipping threshold, when one is set. Each
     * order copies this into its own orders.delivery_charges at the time
     * it's placed, so changing it here never alters existing orders.
     */
    public static function deliveryCharge(?float $subtotal = null): float
    {
        try {
            $info = static::query()->first(['delivery_charges', 'free_shipping_threshold']);
        } catch (\Illuminate\Database\QueryException) {
            // Columns not migrated yet (fresh upload, before running
            // migrations) — treat as free delivery rather than a 500.
            return 0.0;
        }

        $threshold = $info?->free_shipping_threshold;
        if ($subtotal !== null && $threshold !== null && (float) $threshold > 0 && $subtotal >= (float) $threshold) {
            return 0.0;
        }

        return round((float) ($info->delivery_charges ?? 0), 2);
    }

    public function getLogoAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getContactBannerAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getAboutBannerAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getTncBannerAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getFaqBannerAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getCartBannerAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getCheckoutBannerAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getFavoriteBannerAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getShopBannerAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getPrivacyBannerAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getReturnBannerAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getAccountBannerAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getHomeBannerAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getPromoBannerLeftAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getPromoBannerFullAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
    public function getPromoBannerRightAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/site_infos/{$value}";
        } else {
            return null;
        }
    }
}
