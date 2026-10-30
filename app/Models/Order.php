<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'agent_id',
        'coupon_code',
        'discount_amount',
        'admin_discount',
        'admin_discount_type',
        'admin_discount_value',
        'total_amount',
        'delivery_charges',
        'status',
        'payment_method',
        'cart',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'agent_id' => 'integer',
            'cart' => 'array',
            'restocked_at' => 'datetime',
        ];
    }

    public const STATUSES = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

    public function getSubtotalAttribute(): float
    {
        return round((float) $this->items->sum('item_total'), 2);
    }

    /** Contact details as captured at checkout, falling back to the linked account. */
    public function getContactNameAttribute(): ?string
    {
        return $this->customer_name ?: $this->user?->name;
    }

    public function getContactPhoneAttribute(): ?string
    {
        return $this->customer_phone ?: $this->user?->phone;
    }

    public function getContactEmailAttribute(): ?string
    {
        return $this->customer_email ?: $this->user?->email;
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function getProofOfDeliveryAttribute($value)
    {
        if (isset($value) && $value != "" && $value != null) {
            return url("/") . "/upload/orders/deliveries/{$value}";
        } else {
            return null;
        }
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
    public function trails()
    {
        return $this->hasMany(OrderTrail::class, 'order_id');
    }
    public function delivery()
    {
        return $this->hasOne(OrderDelivery::class, 'order_id');
    }
    public function tracking()
    {
        return $this->hasOne(OrderTracking::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
