<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['image'];

    protected function casts(): array
    {
        return [
            "product_id" => "integer"
        ];
    }

    public function getImageAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/products/additional_images/{$value}";
        } else {
            return null;
        }
    }
}
