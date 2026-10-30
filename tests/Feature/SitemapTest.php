<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    private array $backups = [];

    protected function setUp(): void
    {
        parent::setUp();

        // The generator writes straight into public/, so keep the real files.
        foreach (['sitemap.xml', 'robots.txt'] as $file) {
            $path = public_path($file);
            $this->backups[$path] = is_file($path) ? file_get_contents($path) : null;
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->backups as $path => $contents) {
            $contents === null ? @unlink($path) : file_put_contents($path, $contents);
        }

        parent::tearDown();
    }

    public function test_admin_can_generate_sitemap_with_active_products_only(): void
    {
        $this->actingAs(Admin::forceCreate(['name' => 'Admin', 'email' => 'admin@example.test']), 'admin');

        Product::forceCreate(['name' => 'Linen Shirt', 'slug' => 'linen-cotton-shirt', 'price' => 50, 'is_active' => 1]);
        Product::forceCreate(['name' => 'Hidden', 'slug' => 'hidden-scent', 'price' => 50, 'is_active' => 0]);

        $this->post(route('admin.sitemap.generate'))->assertSessionHas('success');

        $xml = file_get_contents(public_path('sitemap.xml'));
        $this->assertStringContainsString('<loc>' . route('home') . '</loc>', $xml);
        $this->assertStringContainsString(route('products.show', 'linen-cotton-shirt'), $xml);
        $this->assertStringNotContainsString('hidden-scent', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));

        // Regenerating keeps a single Sitemap line in robots.txt.
        $this->post(route('admin.sitemap.generate'));
        $this->assertSame(1, substr_count(file_get_contents(public_path('robots.txt')), 'Sitemap:'));
    }

    public function test_guests_cannot_generate_sitemap(): void
    {
        $this->post(route('admin.sitemap.generate'))->assertRedirect();
        $this->assertSame($this->backups[public_path('sitemap.xml')], @file_get_contents(public_path('sitemap.xml')) ?: null);
    }
}
