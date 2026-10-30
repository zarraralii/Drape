<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\GalleryImage;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\SiteInfo;
use App\Utils\Helper;
use Database\Seeders\Support\GarmentArt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * A sample Drape catalogue — collections, products with size/colour
 * variants and stock, hero banners, a lookbook and a welcome discount —
 * with illustrated product images, so the storefront can be reviewed
 * end-to-end before real stock and photography go in.
 *
 *   php artisan db:seed --class=DemoCatalogSeeder
 *
 * Delete the demo products from the admin (or run migrate:fresh) before
 * launch. Skips itself if products already exist.
 */
class DemoCatalogSeeder extends Seeder
{
    private const COLLECTIONS = [
        'Women' => 'Dresses, separates and easy layers in breathable fabrics.',
        'Men' => 'Relaxed shirts, chinos and overshirts cut for comfort.',
        'Eastern' => 'Lawn and cotton kurtas for every day and every occasion.',
        'Essentials' => 'The everyday basics you will reach for again and again.',
    ];

    // name, collection, shape, price, sale price, colours, sizes, featured, description, material
    private const PRODUCTS = [
        ['Everyday Cotton Tee', 'Essentials', 'tee', 1950, null, ['Black', 'White', 'Sage', 'Navy'], ['XS', 'S', 'M', 'L', 'XL'], true,
            'A heavyweight cotton tee with a relaxed, slightly dropped shoulder and a neckline that keeps its shape wash after wash.', "100% combed cotton, 220 gsm\nMachine wash cold, inside out\nDo not tumble dry"],
        ['Relaxed Linen Shirt', 'Men', 'shirt', 4450, null, ['White', 'Sand', 'Sky Blue'], ['S', 'M', 'L', 'XL', 'XXL'], true,
            'Pure linen, cut generously through the body with a soft camp-friendly collar. Gets better with every wear.', "100% European linen\nMachine wash cold, line dry\nIron while slightly damp"],
        ['Oxford Button-Down', 'Men', 'shirt', 4950, null, ['Powder Blue', 'White'], ['S', 'M', 'L', 'XL'], false,
            'A classic oxford with a soft-rolled button-down collar. Smart enough for work, easy enough for the weekend.', "100% cotton oxford\nMachine wash warm\nWarm iron"],
        ['Wrap Midi Dress', 'Women', 'dress', 6950, 5450, ['Terracotta', 'Black', 'Olive'], ['XS', 'S', 'M', 'L'], true,
            'A fluid wrap dress that skims the body and ties at the waist. Fully adjustable and made to move.', "Viscose crepe\nHand wash cold\nCool iron on reverse"],
        ['Tiered Lawn Dress', 'Women', 'dress', 5750, null, ['Ivory', 'Dusty Rose'], ['XS', 'S', 'M', 'L', 'XL'], false,
            'Airy tiers of fine cotton lawn with a gathered waist — the dress for long, hot days.', "100% cotton lawn\nMachine wash cold, gentle\nLine dry in shade"],
        ['Straight Lawn Kurta', 'Eastern', 'kurta', 3950, null, ['Ivory', 'Mint', 'Mustard'], ['S', 'M', 'L', 'XL'], true,
            'A clean straight-cut kurta in crisp printed lawn, with a mandarin collar and side slits.', "100% cotton lawn\nMachine wash cold\nDo not bleach"],
        ['Embroidered Cotton Kurta', 'Eastern', 'kurta', 7450, 6450, ['Bottle Green', 'Maroon'], ['S', 'M', 'L', 'XL'], false,
            'Tonal thread embroidery at the neckline and cuffs on soft, mid-weight cotton.', "100% cotton\nDry clean or hand wash cold\nIron on reverse"],
        ['Wide-Leg Trousers', 'Women', 'trousers', 4250, null, ['Black', 'Camel', 'Ecru'], ['XS', 'S', 'M', 'L'], false,
            'High-rise, wide-leg trousers with front pleats and a fluid drape. Pairs with everything.', "Polyviscose twill\nMachine wash cold\nCool iron"],
        ['Tailored Chinos', 'Men', 'trousers', 4950, null, ['Khaki', 'Navy', 'Olive'], ['30', '32', '34', '36'], false,
            'A slim-straight chino in stretch cotton twill — sharp enough for the office.', "98% cotton, 2% elastane\nMachine wash cold\nWarm iron"],
        ['Pleated Midi Skirt', 'Women', 'skirt', 4650, 3950, ['Charcoal', 'Sage'], ['XS', 'S', 'M', 'L'], false,
            'Knife pleats that swing as you walk, with a comfortable elasticated back waist.', "Recycled polyester satin\nHand wash cold\nHang to dry"],
        ['Fleece Hoodie', 'Essentials', 'hoodie', 5450, null, ['Heather Grey', 'Black', 'Forest'], ['S', 'M', 'L', 'XL', 'XXL'], true,
            'Brushed-back fleece, a double-layer hood and a roomy kangaroo pocket. Your winter go-to.', "80% cotton, 20% polyester fleece\nMachine wash cold\nTumble dry low"],
        ['Cotton Overshirt', 'Men', 'overshirt', 6250, null, ['Olive', 'Camel'], ['S', 'M', 'L', 'XL'], false,
            'A boxy, heavyweight overshirt with twin chest pockets — wear it buttoned or open as a light jacket.', "100% cotton twill, 300 gsm\nMachine wash cold\nWarm iron"],
        ['Canvas Tote', 'Essentials', 'tote', 1850, null, ['Ecru'], [], false,
            'A sturdy everyday tote in heavy cotton canvas, with long handles and an inside pocket.', "100% cotton canvas\nSpot clean"],
    ];

    public function run(): void
    {
        if (Product::exists()) {
            $this->command?->warn('Products already exist — demo catalogue skipped.');

            return;
        }

        $this->call(StoreSetupSeeder::class);

        $collections = [];
        foreach (array_keys(self::COLLECTIONS) as $i => $name) {
            $collections[$name] = Category::firstOrCreate(['name' => $name], ['description' => self::COLLECTIONS[$name], 'sort_order' => $i]);
        }

        $covers = [];
        foreach (self::PRODUCTS as $index => [$name, $collection, $shape, $price, $sale, $colors, $sizes, $featured, $description, $material]) {
            $product = new Product([
                'name' => $name,
                'type' => $sizes ? 'variable' : 'simple',
                'price' => $price,
                'discounted_price' => $sale,
                'short_description' => '<p>' . $description . '</p>',
                'description' => '<p>' . $description . '</p><ul><li>Designed in Pakistan, made in small runs</li><li>True to size — see the size guide</li></ul>',
                'material_care' => $material,
                'published_date' => now()->subDays(count(self::PRODUCTS) - $index)->toDateString(),
            ]);
            $product->is_discounted = $sale !== null;
            $product->slug = Helper::generateUniqueSlug($name, Product::class);
            $product->category_id = $collections[$collection]->id;
            $product->status = 'Approved';
            $product->is_active = 1;
            $product->is_featured = $featured;
            $product->qty = $sizes ? 0 : 24;
            $product->tags = [Str::lower($collection), $shape];
            $product->cover_image = $this->art('products', $shape, $colors[0], $index);
            $product->hover_image = $this->art('products', $shape, $colors[0], $index + 1, true);
            $product->save();
            $product->created_at = now()->subDays(count(self::PRODUCTS) - $index);
            $product->saveQuietly();

            $covers[$collection] ??= $product->getRawOriginal('cover_image');

            foreach (array_slice($colors, 1) as $c => $color) {
                ProductImage::create(['image' => $this->art('products/additional_images', $shape, $color, $index + $c + 2)])
                    ->forceFill(['product_id' => $product->id])->save();
            }

            mt_srand($index + 11);
            foreach ($sizes ? $colors : [] as $color) {
                $colorImage = $this->art('variations', $shape, $color, $index);
                foreach ($sizes as $size) {
                    ProductVariation::create([
                        'product_id' => $product->id,
                        'attributes' => ['Color' => $color, 'Size' => $size],
                        'sku' => strtoupper(Str::limit(Str::slug($name, ''), 6, '') . '-' . Str::limit(Str::slug($color, ''), 4, '') . '-' . $size),
                        // A few sizes sold out so the storefront shows that state.
                        'quantity' => mt_rand(0, 9) === 0 ? 0 : mt_rand(2, 14),
                        'price' => null,
                        'image' => $colorImage,
                    ]);
                }
            }
            $product->syncStockFromVariants();
        }

        foreach ($collections as $name => $category) {
            if ($file = $covers[$name] ?? null) {
                copy(public_path('upload/products/' . $file), $this->ensureDir('categories') . '/' . $file);
                $category->cover_image = $file;
                $category->save();
            }
        }

        Banner::create([
            'eyebrow' => 'Summer edit',
            'headline' => 'Light layers for long days',
            'description' => 'Linen, lawn and breezy cotton — new pieces made to move with you.',
            'cta_label' => 'Shop new arrivals',
            'cta_link' => '/collections/all?sort=newest',
            'sort_order' => 1,
            'is_active' => true,
        ])->forceFill(['background_image' => $this->drape('banners', ['#e9c9b3', '#c98d6c', '#9c5b3c', '#7a4b33'], 21)])->save();

        Banner::create([
            'eyebrow' => 'Eastern',
            'headline' => 'Kurtas for every day',
            'description' => 'Crisp lawn and soft cotton, cut straight and simple.',
            'cta_label' => 'Shop Eastern',
            'cta_link' => '/collections/eastern',
            'sort_order' => 2,
            'is_active' => true,
        ])->forceFill(['background_image' => $this->drape('banners', ['#d4dcc8', '#9fae8e', '#5f7350', '#3d4c35'], 33)])->save();

        $info = SiteInfo::first();
        $info->forceFill([
            'promo_banner_left' => $this->drape('site_infos', ['#efe3d3', '#d6bea0', '#a88763', '#cdb593'], 5, 1000, 1200),
            'promo_banner_left_label' => 'Shop Women',
            'promo_banner_left_link' => '/collections/women',
            'promo_banner_right' => $this->drape('site_infos', ['#dfe3e8', '#a9b4c2', '#5d6b80', '#9aa7b8'], 9, 1000, 1200),
            'promo_banner_right_label' => 'Shop Men',
            'promo_banner_right_link' => '/collections/men',
        ])->save();

        foreach ([['tee', 'Sage'], ['dress', 'Terracotta'], ['kurta', 'Mint'], ['shirt', 'Sky Blue']] as $i => [$shape, $color]) {
            GalleryImage::create([
                'image' => $this->art('galleries', $shape, StoreSetupSeeder::COLORS[$color], $i + 3, true, raw: true),
                'type' => 'image',
                'cta_link' => '/collections/all',
            ]);
        }

        Coupon::firstOrCreate(['code' => 'WELCOME10'], ['type' => 'percent', 'value' => 10, 'min_order_amount' => 3000, 'is_active' => true]);

        $this->command?->info('Demo catalogue created: ' . count(self::PRODUCTS) . ' products in ' . count($collections) . ' collections. Discount code: WELCOME10');
    }

    private function art(string $folder, string $shape, string $color, int $variant, bool $closeUp = false, bool $raw = false): string
    {
        $hex = $raw ? $color : StoreSetupSeeder::COLORS[$color];
        $file = 'demo-' . $shape . '-' . Str::slug($color) . '-' . $variant . ($closeUp ? '-detail' : '') . '.svg';
        file_put_contents($this->ensureDir($folder) . '/' . $file, GarmentArt::product($shape, $hex, $variant, $closeUp));

        return $file;
    }

    private function drape(string $folder, array $palette, int $seed, int $width = 1920, int $height = 1080): string
    {
        $file = 'demo-drape-' . $seed . '.svg';
        file_put_contents($this->ensureDir($folder) . '/' . $file, GarmentArt::drape($palette, $width, $height, $seed));

        return $file;
    }

    private function ensureDir(string $folder): string
    {
        $path = public_path('upload/' . $folder);
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }
}
