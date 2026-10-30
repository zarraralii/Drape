<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'item_id',
        'variation_id',
        'product_name',
        'variant_label',
        'sku',
        'unit_price',
        'qty',
        'item_total',
        'discount',
        'item_options',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'item_id' => 'integer',
            'variation_id' => 'integer',
            'unit_price' => 'float',
            'qty' => 'integer',
            'item_total' => 'float',
            'discount' => 'float',
            'item_options' => 'array',
        ];
    }

    public function item()
    {
        return $this->belongsTo(Product::class, 'item_id')->withTrashed();
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    /** Name as it was when ordered, so renamed/deleted products still read right. */
    public function getDisplayNameAttribute(): string
    {
        return $this->product_name ?: ($this->item->name ?? 'Product');
    }
}
