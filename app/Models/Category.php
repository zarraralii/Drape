<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Helper;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'sort_order'];

    protected function casts(): array
    {
        return [
            "is_active" => "boolean",
            "parent_id" => "integer",
            "sort_order" => "integer",
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Every collection gets a URL handle; "all" is reserved for /collections/all. */
    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if ($category->slug && !$category->isDirty('slug')) {
                return;
            }

            $slug = Str::slug((string) ($category->slug ?: $category->name)) ?: 'collection';
            $category->slug = Helper::generateUniqueSlug($slug === 'all' ? 'all-collection' : $slug, static::class, 'slug', $category->id);
        });
    }
    public function getCoverImageAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/categories/{$value}";
        } else {
            return null;
        }
    }
    public function getBannerImageAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/categories/banners/{$value}";
        } else {
            return null;
        }
    }

    #[Scope]
    protected function isActive(Builder $query)
    {
        return $query->where('is_active', 1);
    }

    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('subcategories');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
