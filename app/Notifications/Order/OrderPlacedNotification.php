<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('New Order Received: ' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new order.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Service:** ' . $this->order->listing->title)
            ->line('**Customer:** ' . $this->order->customer->name)
            ->line('**Amount:** PHP ' . number_format($this->order->total_price, 2))
            ->action('View Order', url('/worker/orders/' . $this->order->id))
            ->line('The customer has been notified to submit payment. You will receive another notification once payment is submitted.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
        ];
    }
}
