<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\SiteInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteInfo::forceCreate([
            'brand_name' => 'Drape',
            'meta_title' => 'Drape – Everyday Clothing',
            'meta_description' => 'Everyday clothing delivered across Pakistan.',
            'google_site_verification' => '<meta name="google-site-verification" content="abc123" />',
        ]);
    }

    private function product(array $attributes = []): Product
    {
        $category = Category::forceCreate(['name' => 'Men', 'is_active' => 1]);

        return Product::forceCreate($attributes + [
            'name' => 'Linen & Cotton Shirt',
            'slug' => 'linen-cotton-shirt',
            'price' => 5000,
            'qty' => 10,
            'is_active' => 1,
            'category_id' => $category->id,
            'short_description' => '<p>A breathable <b>linen</b> shirt.</p>',
        ]);
    }

    public function test_homepage_uses_admin_seo_settings(): void
    {
        $this->get(route('home'))
            ->assertSee('<title>Drape – Everyday Clothing</title>', false)
            ->assertSee('<meta name="description" content="Everyday clothing delivered across Pakistan.">', false)
            ->assertSee('<meta name="google-site-verification" content="abc123">', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('index, follow', false);
    }

    public function test_product_page_has_meta_and_product_schema(): void
    {
        $this->product();

        $html = $this->get(route('products.show', 'linen-cotton-shirt'))->assertOk()->getContent();

        $this->assertStringContainsString('<title>Linen &amp; Cotton Shirt | Drape</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="A breathable linen shirt.">', $html);
        $this->assertStringContainsString('<meta property="og:type" content="product">', $html);

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $schemas = collect($m[1])->map(fn ($json) => json_decode($json, true));
        $product = $schemas->firstWhere('@type', 'Product');

        $this->assertNotNull($product);
        $this->assertSame('Linen & Cotton Shirt', $product['name']);
        $this->assertSame('PKR', $product['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $product['offers']['availability']);
        $this->assertNotNull($schemas->firstWhere('@type', 'BreadcrumbList'));
    }

    public function test_product_meta_overrides_are_used(): void
    {
        $this->product(['meta_title' => 'Linen Shirt Classic', 'meta_description' => 'Custom description.']);

        $this->get(route('products.show', 'linen-cotton-shirt'))
            ->assertSee('<title>Linen Shirt Classic | Drape</title>', false)
            ->assertSee('<meta name="description" content="Custom description.">', false);
    }

    public function test_private_and_filtered_pages_are_noindex(): void
    {
        $this->get(route('login'))->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get(route('cart.index'))->assertSee('<meta name="robots" content="noindex, follow">', false);

        $this->get(route('products.index', ['sort' => 'title']))
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<link rel="canonical" href="' . route('products.index') . '">', false);

        $this->get(route('products.index'))->assertSee('index, follow, max-image-preview:large', false);
    }

    public function test_admin_can_save_seo_fields(): void
    {
        $this->actingAs(Admin::forceCreate(['name' => 'Admin', 'email' => 'admin@example.test']), 'admin');

        $this->post(route('admin.siteinfo'), [
            'brand_name' => 'Drape',
            'meta_title' => 'New Title',
            'meta_description' => 'New description.',
            'google_site_verification' => 'xyz',
        ])->assertSessionHasNoErrors();

        $info = SiteInfo::first();
        $this->assertSame('New Title', $info->meta_title);
        $this->assertSame('xyz', $info->google_site_verification);
    }
}
