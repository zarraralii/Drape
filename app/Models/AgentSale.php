<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentSale extends Model
{
    protected $fillable = [
        'agent_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_amount',
        'commission_rate',
        'commission_amount',
        'sale_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'sale_date' => 'date',
        ];
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
