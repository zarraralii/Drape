<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Faq;
use App\Models\SiteInfo;
use Illuminate\Database\Seeder;

/**
 * Production-safe starting point for the store: Size and Colour options,
 * store settings and a few FAQs. Only fills what's missing, so it's safe
 * to re-run and never overwrites anything the admin has changed.
 *
 *   php artisan db:seed
 */
class StoreSetupSeeder extends Seeder
{
    public const COLORS = [
        'Black' => '#151515', 'White' => '#ffffff', 'Ivory' => '#f4efe3', 'Ecru' => '#e8dfcb', 'Sand' => '#d6c3a1',
        'Camel' => '#b98a55', 'Khaki' => '#a39a6a', 'Olive' => '#6b6b3a', 'Sage' => '#a3ac8f', 'Mint' => '#bcd9c5',
        'Bottle Green' => '#1d4232', 'Forest' => '#2f4a36', 'Sky Blue' => '#9cc2e0', 'Powder Blue' => '#b9cfe2', 'Navy' => '#1e2a44',
        'Heather Grey' => '#b5b3ae', 'Charcoal' => '#3b3b3d', 'Dusty Rose' => '#c4979a', 'Terracotta' => '#b4603f',
        'Maroon' => '#5e1f22', 'Mustard' => '#c9a13b',
    ];

    public const SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '28', '30', '32', '34', '36', 'One Size'];

    public function run(): void
    {
        $this->option('Color', self::COLORS);
        $this->option('Size', array_fill_keys(self::SIZES, null));

        if (!SiteInfo::exists()) {
            SiteInfo::create([
                'brand_name' => 'Drape',
                'tagline' => 'Clothing made to move with you',
                'announcement_text' => 'Free delivery on orders over PKR 5,000 · Cash on delivery nationwide',
                'currency' => 'PKR',
                'delivery_charges' => 250,
                'free_shipping_threshold' => 5000,
                'meta_title' => 'Drape — Everyday clothing in natural fabrics',
                'meta_description' => 'Shop Drape: relaxed shirts, dresses, kurtas and everyday essentials in breathable cotton and linen. Cash on delivery across Pakistan.',
                'about' => '<p>Drape makes easy, considered clothing for everyday life — breathable fabrics, generous cuts and colours that work together season after season.</p>',
                'return_and_exchange' => "Exchanges\nNot the right size? Exchange any unworn item with its tags within 7 days of delivery. Contact us with your order number and we'll arrange it.\n\nReturns\nIf an item arrives damaged or isn't what you ordered, tell us within 48 hours and we'll replace or refund it.\n\nSale items can be exchanged for size only.",
                'shipping_policy' => "We deliver across Pakistan in 3–5 business days. Orders are dispatched within 1–2 business days.\n\nDelivery is free on orders over PKR 5,000; a flat fee applies below that.\n\nPay in cash when your parcel arrives. We'll confirm your order by phone or WhatsApp before dispatch.",
            ]);
        }

        if (!Faq::exists()) {
            $faqs = [
                ['How do I find my size?', 'Every product page has a size guide with body measurements. If you are between sizes, size up for a relaxed fit.'],
                ['How long does delivery take?', 'Orders arrive in 3–5 business days across Pakistan. You can track your order any time with your order number and phone number.'],
                ['Can I pay cash on delivery?', 'Yes — every order is cash on delivery. We will confirm your order by phone or WhatsApp before it ships.'],
                ['Can I exchange an item?', 'Yes. Unworn items with tags can be exchanged within 7 days of delivery. See our refund & exchange policy for details.'],
                ['How should I care for linen and cotton?', 'Wash cold on a gentle cycle, dry in the shade and iron while slightly damp. Natural fabrics soften with every wash.'],
            ];

            foreach ($faqs as $i => [$question, $answer]) {
                Faq::create(['question' => $question, 'answer' => $answer, 'sort' => $i + 1]);
            }
        }
    }

    /** @param array<string, ?string> $values value => swatch */
    private function option(string $name, array $values): void
    {
        $attribute = Attribute::firstOrCreate(['name' => $name]);
        $position = (int) $attribute->values()->max('sort_order');

        foreach ($values as $value => $swatch) {
            $row = AttributeValue::firstOrNew(['attribute_id' => $attribute->id, 'value' => (string) $value]);

            if (!$row->exists) {
                $row->fill(['swatch' => $swatch, 'sort_order' => ++$position]);
                $row->attribute_id = $attribute->id;
                $row->save();
            }
        }
    }
}
