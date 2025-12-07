<?php

namespace App\Events;

use App\Models\ChatMessage;
use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewConversationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public ChatMessage $firstMessage
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->order->worker_id . '.notifications'),
        ];
    }

    public function broadcastWith(): array
    {
        $order = $this->order;
        $message = $this->firstMessage;

        return [
            'id' => $order->id,
            'customer_id' => $order->customer_id,
            'customer_name' => $order->customer->name,
            'customer_avatar' => $order->customer->avatar_url_small,
            'order_number' => $order->order_number,
            'status' => $order->status->label(),
            'status_color' => $order->status->color(),
            'chat_enabled' => $order->isChatEnabled(),
            'unread_count' => 1,
            'last_message' => [
                'message' => $message->message,
                'sender_id' => $message->sender_id,
                'is_file' => $message->isFile(),
                'is_delivery_notice' => $message->isDeliveryNotice(),
            ],
            'messages' => [
                [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'message' => $message->message,
                    'type' => $message->type->value,
                    'file_path' => $message->file_path,
                    'file_name' => $message->file_name,
                    'file_url' => $message->file_url,
                    'created_at' => $message->created_at->toISOString(),
                    'read_at' => null,
                    'is_own' => false,
                ],
            ],
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.new';
    }
}
