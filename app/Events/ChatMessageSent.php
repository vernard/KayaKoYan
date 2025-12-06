<?php

namespace App\Events;

use App\Models\ChatMessage;
use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatMessage $message,
        public Order $order
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.' . $this->order->id . '.chat'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'message' => $this->message->message,
            'type' => $this->message->type->value,
            'file_path' => $this->message->file_path,
            'file_name' => $this->message->file_name,
            'file_url' => $this->message->file_url,
            'created_at' => $this->message->created_at->toISOString(),
            'read_at' => $this->message->read_at?->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
