<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_order_id',
        'product_id',
        'price',
        'quantity',
        'total',
        'size',
    ];

    public function sellerOrder()
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
