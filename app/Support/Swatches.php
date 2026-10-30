<?php

namespace App\Support;

use App\Models\AttributeValue;
use Illuminate\Support\Str;

/**
 * Colour swatches and value ordering for product options. Admins can set
 * an exact hex per attribute value; common fashion colour names fall back
 * to a sensible shade so swatches still render before that's done.
 */
class Swatches
{
    private const NAMED = [
        'black' => '#151515', 'jet black' => '#0e0e0e', 'white' => '#ffffff', 'off white' => '#f4f1ea', 'off-white' => '#f4f1ea',
        'ivory' => '#f6f1e4', 'cream' => '#efe6d2', 'ecru' => '#e8dfcb', 'beige' => '#d9c7a7', 'sand' => '#cdb48e',
        'stone' => '#b9b0a2', 'taupe' => '#8f8174', 'camel' => '#b98a55', 'tan' => '#c19a6b', 'brown' => '#6b4a32',
        'chocolate' => '#4a3022', 'coffee' => '#5b4130', 'mocha' => '#7a5a46', 'rust' => '#a2512d', 'terracotta' => '#b4603f',
        'olive' => '#6b6b3a', 'khaki' => '#a39a6a', 'sage' => '#a3ac8f', 'mint' => '#bcd9c5', 'green' => '#3f6b4b',
        'forest' => '#2f4a36', 'emerald' => '#1f6b52', 'bottle green' => '#1d4232', 'teal' => '#2c6e6f', 'navy' => '#1e2a44',
        'blue' => '#3a5a8c', 'royal blue' => '#2b4ea2', 'sky' => '#9cc2e0', 'sky blue' => '#9cc2e0', 'powder blue' => '#b9cfe2',
        'denim' => '#4a6488', 'indigo' => '#33416b', 'grey' => '#9a9a98', 'gray' => '#9a9a98', 'light grey' => '#c9c8c4',
        'heather grey' => '#b5b3ae', 'charcoal' => '#3b3b3d', 'silver' => '#c4c6c8', 'red' => '#b0302a', 'maroon' => '#5e1f22',
        'burgundy' => '#6a1f2b', 'wine' => '#5a1d2a', 'pink' => '#e7b3bd', 'blush' => '#ecc9c4', 'rose' => '#d58f97',
        'dusty rose' => '#c4979a', 'peach' => '#f1c3a6', 'coral' => '#e57b62', 'mustard' => '#c9a13b', 'yellow' => '#e8cf5a',
        'lemon' => '#efe48d', 'orange' => '#de7a32', 'purple' => '#5e3b73', 'plum' => '#5c2f4a', 'lilac' => '#c3b1d6',
        'lavender' => '#b9b2db', 'mauve' => '#a7838f', 'gold' => '#c8a548', 'multi' => 'linear-gradient(135deg,#d6a2a2,#e8d18f,#9fc3a8,#9db4d6)',
    ];

    public static function isColorOption(string $name): bool
    {
        return in_array(Str::lower(trim($name)), ['color', 'colour', 'colours', 'colors'], true);
    }

    public static function color(string $option, string $value): string
    {
        $stored = self::lookup($option, $value)['swatch'] ?? null;

        if ($stored) {
            return $stored;
        }

        return self::NAMED[Str::lower(trim($value))] ?? '#d8d2c8';
    }

    public static function sortOrder(string $option, string $value): string
    {
        $row = self::lookup($option, $value);

        // Fall back to the usual size run when the admin hasn't ordered values.
        $sizes = ['xxs', 'xs', 's', 'm', 'l', 'xl', 'xxl', '2xl', '3xl', '4xl'];
        $sizeIndex = array_search(Str::lower($value), $sizes, true);

        return sprintf('%05d-%05d-%s', $row['sort_order'] ?? 99999, $sizeIndex === false ? 99999 : $sizeIndex, $value);
    }

    /** Loaded once per request, like App\Support\Store. */
    private static function lookup(string $option, string $value): ?array
    {
        $attributes = request()->attributes;

        if (!$attributes->has('store.swatches')) {
            $values = [];

            try {
                foreach (AttributeValue::with('attribute')->orderBy('sort_order')->orderBy('id')->get() as $index => $row) {
                    $values[Str::lower(($row->attribute->name ?? '') . '|' . $row->value)] = ['swatch' => $row->swatch, 'sort_order' => $index];
                }
            } catch (\Illuminate\Database\QueryException) {
                // Columns not migrated yet — fall back to names only.
            }

            $attributes->set('store.swatches', $values);
        }

        return $attributes->get('store.swatches')[Str::lower($option . '|' . $value)] ?? null;
    }
}
