<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $guarded = [];

    /**
     * @return BelongsTo<User,$this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[\Override]
    protected static function booted()
    {
        static::creating(function (Transaction $transaction): void {
            $transaction->reference = (string) Str::ulid();
        });
    }

    /**
     * @return BelongsTo<PaymentMethod,$this>
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function notifySellerAboutWithdrawalApproval(): void
    {
        $this->user->notify(new \App\Notifications\SellerWithdrawalApprovedNotification($this));
    }
}
