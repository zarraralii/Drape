<?php

namespace App\Models;

use App\Support\Swatches;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'type', 'published_date', 'price', 'is_discounted', 'discounted_price', 'discount_start_date', 'discount_end_date', 'short_description', 'description', 'material_care', 'tags', 'qty', 'meta_title', 'meta_description'];

    protected function casts(): array
    {
        return [
            "tags" => "array",
            "is_active" => "boolean",
            "is_featured" => "boolean",
            "is_deal" => "boolean"
        ];
    }

    public function getCoverImageAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/products/{$value}";
        } else {
            return null;
        }
    }

    public function getCoverImageThumbnailAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/products/thumbnails/{$value}";
        } else {
            return null;
        }
    }

    public function getHoverImageAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/products/{$value}";
        } else {
            return $this->cover_image;
        }
    }

    public function getIsOnSaleAttribute(): bool
    {
        if (!$this->is_discounted || $this->discounted_price === null || $this->discounted_price >= $this->price) {
            return false;
        }

        $now = now();

        if ($this->discount_start_date && $now->lt($this->discount_start_date)) {
            return false;
        }

        if ($this->discount_end_date && $now->gt($this->discount_end_date)) {
            return false;
        }

        return true;
    }

    public function getCurrentPriceAttribute()
    {
        return $this->is_on_sale ? $this->discounted_price : $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (!$this->is_on_sale || (float) $this->price <= 0) {
            return 0;
        }

        return (int) round((($this->price - $this->discounted_price) / $this->price) * 100);
    }

    public function getRatingAttribute()
    {
        $average = $this->reviews()->visible()->whereNotNull('rating')->avg('rating');

        return $average === null ? null : number_format((float) $average, 1, '.', '');
    }

    public function getReviewCountsAttribute()
    {
        return $this->reviews()->visible()->count();
    }

    public function isVariable(): bool
    {
        return $this->type === 'variable' && $this->variations->isNotEmpty();
    }

    /**
     * A simple product with a null qty is unlimited stock; a variable
     * product is in stock while any one of its variants is.
     */
    public function getInStockAttribute(): bool
    {
        if ($this->isVariable()) {
            return $this->variations->contains(fn (ProductVariation $variant) => $variant->quantity > 0);
        }

        return $this->qty === null || $this->qty > 0;
    }

    public function getIsNewAttribute(): bool
    {
        return $this->created_at !== null && $this->created_at->gt(now()->subDays(30));
    }

    /**
     * Lowest and highest selling price across variants, so cards can show
     * "From PKR 2,400" when sizes are priced differently.
     *
     * @return array{0: float, 1: float}
     */
    public function priceRange(): array
    {
        if (!$this->isVariable()) {
            $price = (float) $this->current_price;

            return [$price, $price];
        }

        $prices = $this->variations->map(fn (ProductVariation $variant) => $variant->unitPrice($this));

        return [(float) $prices->min(), (float) $prices->max()];
    }

    /**
     * The product's options (e.g. Colour, Size) in display order, each with
     * its values in the order the admin set on the attribute.
     *
     * @return Collection<int, array{name: string, is_color: bool, values: array<int, array{value: string, swatch: ?string}>}>
     */
    public function options(): Collection
    {
        if (!$this->isVariable()) {
            return collect();
        }

        $names = [];
        foreach ($this->variations as $variant) {
            foreach (array_keys($variant->optionValues()) as $name) {
                $names[$name] = true;
            }
        }

        return collect(array_keys($names))
            ->sortBy(fn (string $name) => Swatches::isColorOption($name) ? 0 : 1)
            ->values()
            ->map(function (string $name) {
                $values = $this->variations
                    ->map(fn (ProductVariation $variant) => (string) ($variant->optionValues()[$name] ?? ''))
                    ->filter(fn (string $value) => $value !== '')
                    ->unique()
                    ->sortBy(fn (string $value) => Swatches::sortOrder($name, $value))
                    ->values();

                return [
                    'name' => $name,
                    'is_color' => Swatches::isColorOption($name),
                    'values' => $values->map(fn (string $value) => [
                        'value' => $value,
                        'swatch' => Swatches::isColorOption($name) ? Swatches::color($name, $value) : null,
                    ])->all(),
                ];
            });
    }

    /** Colour values for the swatch dots under a product card. */
    public function colorSwatches(): array
    {
        return $this->options()->firstWhere('is_color', true)['values'] ?? [];
    }

    /** Everything the product page's variant picker needs, as plain data. */
    public function variantPayload(): array
    {
        return $this->variations->map(function (ProductVariation $variant) {
            $compareAt = $variant->compareAtPrice($this);

            return [
                'id' => $variant->id,
                'options' => $variant->optionValues(),
                'label' => $variant->label,
                'price' => money($variant->unitPrice($this)),
                'compare_at' => $compareAt !== null ? money($compareAt) : null,
                'stock' => (int) $variant->quantity,
                'image' => $variant->image,
            ];
        })->values()->all();
    }

    /** Keep products.qty equal to the sum of variant stock for listings and filters. */
    public function syncStockFromVariants(): void
    {
        if ($this->type !== 'variable') {
            return;
        }

        $this->qty = (int) $this->variations()->sum('quantity');
        $this->saveQuietly();
    }

    #[Scope]
    protected function isActive(Builder $query)
    {
        return $query->where('is_active', 1);
    }

    public function gallery()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class)->orderBy('id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'product_id', 'id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'item_id');
    }
}
