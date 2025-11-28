<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property float|null $platform_fee
 * @property float|null $delivery_fee
 */
class Order extends Model
{
    use HasFactory;

    protected $casts = [
        'platform_fee_added' => 'boolean',
        'product_cost'       => 'float',
        'commission'         => 'float',
        'delivery_fee'       => 'float',
        'platform_fee'       => 'float',
        'vat'                => 'float',
        'grand_total'        => 'float',
        'balance'            => 'float',
        'is_multi_seller'    => 'boolean',
        'total_sellers'      => 'integer',
        'completed_order'    => 'boolean',
    ];

    /**
     * @return BelongsTo<User,$this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<SellerOrder,$this>
     */
    public function sellerOrders(): HasMany
    {
        return $this->hasMany(SellerOrder::class);
    }
}
