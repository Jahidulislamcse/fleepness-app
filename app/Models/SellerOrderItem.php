<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    /**
     * @return BelongsTo<SellerOrder,$this>
     */
    public function sellerOrder(): BelongsTo
    {
        return $this->belongsTo(SellerOrder::class);
    }

    /**
     * @return BelongsTo<Product,$this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
