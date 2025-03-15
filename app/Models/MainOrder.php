<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainOrder extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'order_date' => 'datetime',
    ];

    public function customerInfo()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id', 'id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderItem::class, 'main_order_id', 'id');
    }
}
