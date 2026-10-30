<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'type',
        'referral_code',
        'commission_rate',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function sales()
    {
        return $this->hasMany(AgentSale::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function totalCommission(): float
    {
        return (float) $this->sales()->sum('commission_amount');
    }

    public function totalQuantity(): int
    {
        return (int) $this->sales()->sum('quantity');
    }
}
