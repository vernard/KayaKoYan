<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Notifications\Order\PaymentSubmittedNotification;
use App\Notifications\Order\PaymentReceivedNotification;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $order = $payment->order;
        $order->transitionTo(OrderStatus::PaymentSubmitted);

        $order->worker->notify(new PaymentSubmittedNotification($order));
    }

    public function updated(Payment $payment): void
    {
        if ($payment->isDirty('status')) {
            $order = $payment->order;

            if ($payment->status === PaymentStatus::Verified) {
                $order->markPaymentReceived();
                $order->customer->notify(new PaymentReceivedNotification($order));
            }
        }
    }
}
