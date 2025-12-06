<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompletedNotification extends Notification implements ShouldQueue
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
            ->subject('Order Completed: ' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Congratulations! Your order has been completed and accepted by the customer.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Service:** ' . $this->order->listing->title)
            ->line('**Customer:** ' . $this->order->customer->name)
            ->line('**Amount Earned:** PHP ' . number_format($this->order->total_price, 2))
            ->action('View Order', url('/worker/orders/' . $this->order->id))
            ->line('Thank you for being a valued worker on Kaya Ko Yan!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
        ];
    }
}
