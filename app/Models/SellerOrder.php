<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read User $seller
 */
class SellerOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'delivery_start_time' => 'datetime',
        'delivery_end_time' => 'datetime',
        'rider_assigned' => 'boolean',

        'status' => \App\Enums\SellerOrderStatus::class,
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
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

    public function notifySellerAboutNewOrderFromBuyer()
    {
        $this->seller->notify(new \App\Notifications\OrderReceivedFromBuyer($this));
    }
}
