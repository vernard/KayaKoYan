<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Notifications\Order\OrderPlacedNotification;
use App\Notifications\Order\WorkDeliveredNotification;
use App\Notifications\Order\OrderCompletedNotification;

class OrderObserver
{
    public function created(Order $order): void
    {
        $order->worker->notify(new OrderPlacedNotification($order));
    }

    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            $newStatus = $order->status;

            if ($newStatus === OrderStatus::Delivered) {
                $order->customer->notify(new WorkDeliveredNotification($order));
            }

            if ($newStatus === OrderStatus::Completed) {
                $order->worker->notify(new OrderCompletedNotification($order));
            }
        }
    }
}
