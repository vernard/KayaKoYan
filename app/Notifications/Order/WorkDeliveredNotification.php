<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkDeliveredNotification extends Notification implements ShouldQueue
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
            ->subject('Work Delivered: ' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great news! The worker has delivered the completed work for your order.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Service:** ' . $this->order->listing->title)
            ->line('**Worker:** ' . $this->order->worker->name)
            ->line('Please review the delivery and accept it if you are satisfied with the work.')
            ->action('Review Delivery', route('customer.orders.show', $this->order))
            ->line('If you have any concerns, you can message the worker through the chat feature.')
            ->line('Thank you for using Kaya Ko Yan!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
        ];
    }
}
