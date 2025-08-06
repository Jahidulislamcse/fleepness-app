<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'seller_id',
        'delivery_status',
        'delivery_message',
        'delivery_start_time',
        'delivery_end_time',
        'product_total',
        'commission_amount',
        'rider_assigned',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(SellerOrderItem::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class);
    }
}
