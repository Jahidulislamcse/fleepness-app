<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read User $seller
 */
class SellerOrder extends Model
{
    use HasFactory;

    protected $casts = [
        'delivery_start_time' => 'datetime',
        'delivery_end_time' => 'datetime',
        'rider_assigned' => 'boolean',

        'status' => \App\Enums\SellerOrderStatus::class,

        'product_cost' => 'float',
        'commission' => 'float',
        'vat' => 'float',
        'delivery_fee' => 'float',
        'balance' => 'float',
    ];
    /**
     * @return BelongsTo<Order,$this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return HasMany<SellerOrderItem,$this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SellerOrderItem::class);
    }

    /**
     * @return BelongsTo<User,$this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User,$this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function notifySellerAboutNewOrderFromBuyer()
    {
        $this->seller->notify(new \App\Notifications\OrderReceivedFromBuyer($this));
    }

    public function notifyBuyerAboutOrderStatus()
    {
        $this->customer->notify(new \App\Notifications\OrderStatusChanged($this));
    }
}
