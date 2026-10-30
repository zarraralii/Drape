<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    protected $fillable = ['value', 'attribute_id', 'swatch', 'sort_order'];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
