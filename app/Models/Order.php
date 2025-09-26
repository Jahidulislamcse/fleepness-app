<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_code',
        'is_multi_seller',
        'total_sellers',
        'delivery_model',
        'platform_fee',
        'vat',
        'total_amount',
        'commission_total',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sellerOrders()
    {
        return $this->hasMany(SellerOrder::class);
    }
}
