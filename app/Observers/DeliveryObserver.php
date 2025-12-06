<?php

namespace App\Observers;

use App\Enums\ChatMessageType;
use App\Enums\OrderStatus;
use App\Models\ChatMessage;
use App\Models\Delivery;

class DeliveryObserver
{
    public function created(Delivery $delivery): void
    {
        $order = $delivery->order;

        $order->transitionTo(OrderStatus::Delivered);

        ChatMessage::create([
            'order_id' => $order->id,
            'sender_id' => $order->worker_id,
            'message' => $delivery->notes ?? 'Work has been delivered. Please review and accept.',
            'type' => ChatMessageType::DeliveryNotice,
        ]);
    }
}
