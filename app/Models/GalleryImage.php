<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    protected $fillable = ['image', 'type', 'cta_link'];

    public function getImageAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/galleries/{$value}";
        } else {
            return null;
        }
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }
}
