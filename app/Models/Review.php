<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'name',
        'email',
        'review',
        'rating',
        'media',
        'is_hidden',
        'admin_reply',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'media' => 'array',
            'is_hidden' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    /**
     * Hidden reviews stay in the table — so the customer still can't post
     * another one for the same order — but drop out of everything public:
     * the product page list, its average rating and its review count.
     * Deleting a review is what frees the order up to be reviewed again.
     */
    #[Scope]
    protected function visible(Builder $query)
    {
        return $query->where('is_hidden', false);
    }

    /**
     * The bare stored filenames, for cleaning up uploads on delete — the
     * `media` accessor only ever hands back display URLs.
     */
    public function mediaFilenames(): array
    {
        return collect($this->castAttribute('media', $this->getRawOriginal('media')) ?? [])
            ->pluck('file')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Each stored entry is `{"file": "...", "type": "image"|"video"}` —
     * turn that into `{"url": "...", "type": "..."}` for display, since
     * the bare filename on its own isn't servable.
     */
    public function getMediaAttribute($value)
    {
        $items = $this->castAttribute('media', $value) ?? [];

        return collect($items)
            ->filter(fn ($item) => !empty($item['file']))
            ->map(fn ($item) => [
                'url' => url('/') . '/upload/reviews/' . $item['file'],
                'type' => $item['type'] ?? 'image',
            ])
            ->values()
            ->all();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

}
