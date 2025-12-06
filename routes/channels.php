<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Default user channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for order chat - only customer and worker can access
Broadcast::channel('order.{orderId}.chat', function (User $user, int $orderId) {
    $order = Order::find($orderId);

    if (!$order) {
        return false;
    }

    return $user->id === $order->customer_id || $user->id === $order->worker_id;
});

// Presence channel for online status in order chat
Broadcast::channel('order.{orderId}.presence', function (User $user, int $orderId) {
    $order = Order::find($orderId);

    if (!$order) {
        return false;
    }

    if ($user->id === $order->customer_id || $user->id === $order->worker_id) {
        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }

    return false;
});
