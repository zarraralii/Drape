<?php

namespace App\Utils;

use Illuminate\Support\Str;

class Helper
{
    /**
     * Slugify $name and make sure it's unique within $modelClass's table,
     * appending -2, -3, ... as needed. Pass $ignoreId when updating an
     * existing row so it doesn't collide with its own current slug.
     */
    public static function generateUniqueSlug(string $name, string $modelClass, string $column = 'slug', $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        // Soft-deleted rows still hold their slug in a unique index.
        $query = fn () => in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass), true)
            ? $modelClass::withTrashed()
            : $modelClass::query();

        while (
            $query()->where($column, $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
