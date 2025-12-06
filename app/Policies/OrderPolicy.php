<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->customer_id || $user->id === $order->worker_id;
    }

    public function pay(User $user, Order $order): bool
    {
        return $user->id === $order->customer_id && $order->status === OrderStatus::PendingPayment;
    }

    public function accept(User $user, Order $order): bool
    {
        return $user->id === $order->customer_id && $order->status === OrderStatus::Delivered;
    }

    public function download(User $user, Order $order): bool
    {
        return $user->id === $order->customer_id && $order->canDownloadDigitalProduct();
    }

    public function chat(User $user, Order $order): bool
    {
        return $user->id === $order->customer_id || $user->id === $order->worker_id;
    }

    public function verifyPayment(User $user, Order $order): bool
    {
        return $user->id === $order->worker_id && $order->status === OrderStatus::PaymentSubmitted;
    }

    public function deliver(User $user, Order $order): bool
    {
        return $user->id === $order->worker_id && in_array($order->status, [
            OrderStatus::PaymentReceived,
            OrderStatus::InProgress,
        ]);
    }
}
