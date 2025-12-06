<?php

namespace App\Policies;

use App\Models\ChatMessage;
use App\Models\User;

class ChatMessagePolicy
{
    public function view(User $user, ChatMessage $message): bool
    {
        $order = $message->order;
        return $user->id === $order->customer_id || $user->id === $order->worker_id;
    }

    public function create(User $user): bool
    {
        return true;
    }
}
