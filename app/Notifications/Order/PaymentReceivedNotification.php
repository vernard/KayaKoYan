<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
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
        $message = (new MailMessage)
            ->subject('Payment Confirmed: ' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great news! Your payment has been verified and confirmed.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Service:** ' . $this->order->listing->title)
            ->line('**Amount:** PHP ' . number_format($this->order->total_price, 2));

        if ($this->order->listing->isDigitalProduct()) {
            $message->line('Since this is a digital product, your order is now complete and ready for download!')
                ->action('Download Now', route('customer.orders.download', $this->order));
        } else {
            $message->line('The worker will now begin working on your order. You will be notified once the work is delivered.')
                ->action('View Order', route('customer.orders.show', $this->order));
        }

        return $message->line('Thank you for using Kaya Ko Yan!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
        ];
    }
}
