<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->order->latestPayment;

        return (new MailMessage)
            ->subject('Payment Submitted: ' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A customer has submitted payment for their order.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Customer:** ' . $this->order->customer->name)
            ->line('**Payment Method:** ' . $payment->method->label())
            ->line('**Reference Number:** ' . $payment->reference_number)
            ->line('**Amount:** PHP ' . number_format($payment->amount, 2))
            ->action('Verify Payment', url('/worker/orders/' . $this->order->id))
            ->line('Please verify the payment and mark it as received to proceed with the order.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
        ];
    }
}
