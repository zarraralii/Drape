<?php

namespace App\Models;

use App\Support\Swatches;
use Illuminate\Database\Eloquent\Model;

/**
 * One purchasable combination of a product's options, e.g.
 * {"Color": "Black", "Size": "M"}, with its own stock and an optional
 * price (null = the product's current price, sale included).
 */
class ProductVariation extends Model
{
    protected $fillable = ['product_id', 'attributes', 'sku', 'quantity', 'price', 'image'];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'quantity' => 'integer',
        ];
    }

    public function getImageAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/variations/{$value}";
        } else {
            return null;
        }
    }

    /**
     * The option map, e.g. ["Color" => "Black", "Size" => "M"]. Always use
     * this rather than ->attributes: inside any Model subclass that name
     * resolves to Eloquent's own protected $attributes array instead.
     */
    public function optionValues(): array
    {
        return (array) $this->getAttribute('attributes');
    }

    /** "Black / M" — colour first, matching the product page. */
    public function getLabelAttribute(): string
    {
        return collect($this->optionValues())
            ->sortBy(fn ($value, $name) => Swatches::isColorOption((string) $name) ? 0 : 1)
            ->values()
            ->implode(' / ');
    }

    public function unitPrice(?Product $product = null): float
    {
        if ($this->price !== null && $this->price !== '') {
            return (float) $this->price;
        }

        return (float) ($product ?? $this->product)->current_price;
    }

    public function compareAtPrice(?Product $product = null): ?float
    {
        $product ??= $this->product;

        if (($this->price === null || $this->price === '') && $product->is_on_sale) {
            return (float) $product->price;
        }

        return null;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
