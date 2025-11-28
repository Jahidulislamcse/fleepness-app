<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Number;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SellerWithdrawalApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Transaction $transaction) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'sms', 'database', 'broadcast'];
    }

    public function toSms(object $notifiable): string
    {
        $withdrawAmount = Number::currency((float) $this->transaction->amount, 'BDT');

        return "Your withdrawal request of {$withdrawAmount} has been approved. "
            ."Transaction ID: {$this->transaction->reference}.";
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $withdrawAmount = Number::currency((float) $this->transaction->amount, 'BDT');

        return (new MailMessage)
            ->subject('Your Withdrawal Has Been Approved')
            ->greeting("Hello {$notifiable->name},")
            ->line("Good news! Your withdrawal request of **{$withdrawAmount}** has been approved.")
            ->line('The funds are now being processed and will be on their way shortly.')
            ->line("Transaction ID: **{$this->transaction->reference}**")
            // ->action('View Transaction Details', url("/transactions/{$this->transaction->reference}"))
            ->line('If you did not request this withdrawal, please contact support immediately.')
            ->salutation('Thank you for using Moby!');
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): array
    {
        $withdrawAmount = Number::currency((float) $this->transaction->amount, 'BDT');

        return [
            'notification' => [
                'title' => 'Withdrawal Approved',
                'body' => "Your withdrawal of {$withdrawAmount} has been approved.",
            ],
            'transaction_id' => $this->transaction->reference,
        ];
    }

    public function broadcastAs(): string
    {
        return 'withdrawal_approved';
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return 'approved' === $this->transaction->status;
    }

    /**
     * Storeable notification array data.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->reference,
            'amount' => $this->transaction->amount,
            'status' => 'approved',
            'message' => 'Your withdrawal has been approved.',
        ];
    }
}
