<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ProductController;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductGalleryTest extends TestCase
{
    use RefreshDatabase;

    private array $createdFiles = [];

    protected function tearDown(): void
    {
        // Uploads go straight into public/ (FileManagerTrait), so tidy up.
        foreach ($this->createdFiles as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_additional_images_are_saved_removed_and_shown(): void
    {
        $this->actingAs(Admin::forceCreate(['name' => 'Admin', 'email' => 'admin@example.test']), 'admin');
        $category = Category::forceCreate(['name' => 'Women', 'is_active' => 1]);

        // Create with two additional images.
        $this->post(route('admin.add-product'), $this->productFields($category) + [
            'cover_image' => $this->image('cover.jpg'),
            'additional_images' => [$this->image('a.jpg'), $this->image('b.jpg')],
        ])->assertSessionHasNoErrors();

        $product = Product::firstOrFail();
        $this->assertSame(2, $product->gallery()->count());
        $this->trackUploads($product);

        // Update: remove one, add one.
        $toRemove = $product->gallery()->first();
        $this->post(route('admin.add-product'), $this->productFields($category) + [
            'id' => $product->id,
            'deleted_images' => [$toRemove->id],
            'additional_images' => [$this->image('c.jpg')],
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, $product->gallery()->count());
        $this->assertNull(ProductImage::find($toRemove->id));
        $this->assertFileDoesNotExist(public_path('upload/products/additional_images/' . $toRemove->getRawOriginal('image')));
        $this->trackUploads($product);

        // Storefront shows every gallery image as a thumbnail.
        $page = $this->get(route('products.show', $product->slug))->assertOk();
        foreach ($product->gallery as $image) {
            $page->assertSee($image->image, false);
        }
        $page->assertSee('data-gallery-track', false);
    }

    public function test_gallery_is_capped(): void
    {
        $this->actingAs(Admin::forceCreate(['name' => 'Admin', 'email' => 'admin@example.test']), 'admin');
        $category = Category::forceCreate(['name' => 'Women', 'is_active' => 1]);

        $tooMany = [];
        for ($i = 0; $i <= ProductController::MAX_GALLERY_IMAGES; $i++) {
            $tooMany[] = $this->image("g$i.jpg");
        }

        $this->post(route('admin.add-product'), $this->productFields($category) + [
            'cover_image' => $this->image('cover.jpg'),
            'additional_images' => $tooMany,
        ])->assertSessionHasErrors('additional_images');

        $this->assertSame(0, ProductImage::count());
    }

    private function productFields(Category $category): array
    {
        return [
            'name' => 'Gallery Test',
            'type' => 'simple',
            'category_id' => $category->id,
            'price' => 1000,
            'qty' => 5,
            'published_date' => now()->toDateString(),
            'short_description' => 'Short',
        ];
    }

    private function image(string $name): UploadedFile
    {
        return UploadedFile::fake()->image($name, config('media.product.min_width'), config('media.product.min_height'));
    }

    private function trackUploads(Product $product): void
    {
        $product->refresh();

        foreach (['cover_image' => 'upload/products/', 'cover_image_thumbnail' => 'upload/products/thumbnails/'] as $column => $folder) {
            if ($file = $product->getRawOriginal($column)) {
                $this->createdFiles[] = public_path($folder . basename($file));
            }
        }

        foreach ($product->gallery as $image) {
            $this->createdFiles[] = public_path('upload/products/additional_images/' . $image->getRawOriginal('image'));
        }
    }
}
