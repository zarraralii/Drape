<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTrail extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
        ];
    }
}
