<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class ChatService
{
    /**
     * Get all orders with chat messages for a user.
     */
    public function getConversationsForUser(User $user): Collection
    {
        $query = Order::query()
            ->with(['customer', 'worker', 'listing', 'chatMessages.sender'])
            ->withCount(['chatMessages as unread_count' => function ($query) use ($user) {
                $query->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at');
            }])
            ->withMax('chatMessages', 'created_at')
            ->whereHas('chatMessages');

        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        } else {
            $query->where('worker_id', $user->id);
        }

        return $query->orderByDesc('chat_messages_max_created_at')->get();
    }

    /**
     * Get total unread message count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        $query = ChatMessage::query()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->whereHas('order', function ($query) use ($user) {
                if ($user->isCustomer()) {
                    $query->where('customer_id', $user->id);
                } else {
                    $query->where('worker_id', $user->id);
                }
            });

        return $query->count();
    }

    /**
     * Get recent conversations for notification display.
     */
    public function getRecentConversations(User $user, int $limit = 5): Collection
    {
        return $this->getConversationsForUser($user)->take($limit);
    }

    /**
     * Get the last message for an order.
     */
    public function getLastMessage(Order $order): ?ChatMessage
    {
        return $order->chatMessages()->with('sender')->latest()->first();
    }
}
