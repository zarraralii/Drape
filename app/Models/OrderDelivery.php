<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDelivery extends Model
{
    protected $fillable = [
        'order_id',
        'full_address',
        'country',
        'postal_code',
        'state',
        'city'
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
        ];
    }
}
