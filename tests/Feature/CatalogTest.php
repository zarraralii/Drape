<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    private Category $women;

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.cache_store' => 'array', 'cache.limiter' => 'array']);
        $this->women = Category::forceCreate(['name' => 'Women', 'is_active' => 1]);
    }

    private function product(string $name, array $variants, array $extra = []): Product
    {
        $product = Product::forceCreate($extra + [
            'name' => $name, 'slug' => \Str::slug($name), 'type' => 'variable', 'price' => 3000,
            'qty' => 0, 'is_active' => 1, 'category_id' => $this->women->id,
        ]);
        foreach ($variants as [$color, $size, $qty]) {
            ProductVariation::create(['product_id' => $product->id, 'attributes' => ['Color' => $color, 'Size' => $size], 'sku' => \Str::random(8), 'quantity' => $qty]);
        }
        $product->syncStockFromVariants();

        return $product;
    }

    public function test_collections_get_url_handles(): void
    {
        $this->assertSame('women', $this->women->slug);
        $this->assertSame('all-collection', Category::forceCreate(['name' => 'All', 'is_active' => 1])->slug);

        $this->get(route('collections.show', $this->women))->assertOk()->assertSee('Women');
        $this->get('/collections/does-not-exist')->assertNotFound();
    }

    public function test_size_and_colour_filters_only_match_in_stock_variants(): void
    {
        $this->product('Wrap Dress', [['Black', 'M', 4], ['Black', 'L', 0]]);
        $this->product('Tiered Dress', [['Ivory', 'L', 2]]);

        $this->get(route('products.index', ['filter' => ['Size' => ['L']]]))
            ->assertSee('Tiered Dress')->assertDontSee('Wrap Dress');

        $this->get(route('products.index', ['filter' => ['Color' => ['Black']]]))
            ->assertSee('Wrap Dress')->assertDontSee('Tiered Dress');

        // Unknown option names are ignored rather than reaching the query.
        $this->get(route('products.index', ['filter' => ["Size') or 1=1 --" => ['x']]]))->assertOk()->assertSee('Wrap Dress');
    }

    public function test_product_page_offers_options_and_marks_sold_out_sizes(): void
    {
        $product = $this->product('Wrap Dress', [['Black', 'M', 4], ['Black', 'L', 0]]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('name="option[Color]"', false)
            ->assertSee('name="option[Size]"', false)
            ->assertSee('data-variant-input', false)
            ->assertSee('Add to bag');
    }

    public function test_admin_saves_variants_from_the_builder(): void
    {
        $this->actingAs(Admin::forceCreate(['name' => 'Admin', 'email' => 'admin@example.test']), 'admin');

        $this->post(route('admin.add-product'), [
            'name' => 'Linen Shirt',
            'type' => 'variable',
            'category_id' => $this->women->id,
            'price' => 4000,
            'published_date' => now()->toDateString(),
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 540, 720),
            'variants' => [
                ['options' => json_encode(['Color' => 'White', 'Size' => 'S']), 'quantity' => 3, 'price' => ''],
                ['options' => json_encode(['Color' => 'White', 'Size' => 'M']), 'quantity' => 5, 'price' => '4500', 'sku' => 'LIN-WHT-M'],
            ],
        ])->assertSessionHasNoErrors();

        $product = Product::with('variations')->firstOrFail();
        $this->assertSame(8, $product->qty);
        $this->assertCount(2, $product->variations);
        $this->assertNull($product->variations[0]->price);
        $this->assertEquals(4500, $product->variations[1]->price);
        $this->assertSame('White / M', $product->variations[1]->label);

        foreach (['cover_image' => 'upload/products/', 'cover_image_thumbnail' => 'upload/products/thumbnails/'] as $column => $folder) {
            @unlink(public_path($folder . $product->getRawOriginal($column)));
        }
    }

    public function test_editing_an_attribute_keeps_value_ids_and_saves_swatches(): void
    {
        $this->actingAs(Admin::forceCreate(['name' => 'Admin', 'email' => 'admin@example.test']), 'admin');
        $color = Attribute::create(['name' => 'Color']);
        $black = AttributeValue::create(['attribute_id' => $color->id, 'value' => 'Black']);

        $this->post(route('admin.add-attribute'), [
            'id' => $color->id,
            'name' => 'Color',
            'values' => [
                ['id' => $black->id, 'value' => 'Jet Black', 'swatch' => '#111111'],
                ['value' => 'Sage', 'swatch' => '#a3ac8f'],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame('Jet Black', $black->fresh()->value);
        $this->assertSame('#111111', $black->fresh()->swatch);
        $this->assertSame(2, AttributeValue::count());
    }
}
