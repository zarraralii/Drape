<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'usage_limit',
        'used_count',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'expires_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired() && !$this->isExhausted();
    }

    public function meetsMinimum(float $subtotal): bool
    {
        return $this->min_order_amount === null || $subtotal >= (float) $this->min_order_amount;
    }

    /**
     * Amount knocked off $subtotal — 0 below the minimum order, and never
     * more than the subtotal itself.
     */
    public function discountFor(float $subtotal): float
    {
        if (!$this->meetsMinimum($subtotal)) {
            return 0.0;
        }

        $discount = $this->type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return round(min($discount, $subtotal), 2);
    }

    /**
     * Counts one use, but only while under the usage limit — a single
     * conditional UPDATE, so two simultaneous checkouts can't both take
     * the last use. Returns false when the limit has already been hit.
     */
    public function claimUse(): bool
    {
        return static::whereKey($this->id)
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->increment('used_count') === 1;
    }
}
