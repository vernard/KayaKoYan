<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $cancelledBy = 'system'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order Cancelled: ' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('An order has been cancelled.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Service:** ' . $this->order->listing->title)
            ->line('If you have any questions, please contact support.')
            ->line('Thank you for using Kaya Ko Yan.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'cancelled_by' => $this->cancelledBy,
        ];
    }
}
