<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Image + text blocks on the "Our Story" page, shown in alternating
 * left/right layout in sort_order.
 */
class StorySection extends Model
{
    protected $fillable = ['title', 'description', 'cta_label', 'cta_link', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getImageAttribute($value)
    {
        if (isset($value) && $value != '' && $value != null) {
            return url('/') . "/upload/story/{$value}";
        }

        return null;
    }

    #[Scope]
    protected function isActive(Builder $query)
    {
        return $query->where('is_active', 1);
    }
}
