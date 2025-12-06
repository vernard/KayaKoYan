<?php

namespace App\Http\Controllers;

use App\Enums\ChatMessageType;
use App\Models\ChatMessage;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function index(Order $order): View
    {
        $this->authorize('chat', $order);

        $order->load(['listing', 'customer', 'worker', 'chatMessages.sender']);

        ChatMessage::where('order_id', $order->id)
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('chat.index', compact('order'));
    }

    public function store(Request $request, Order $order): JsonResponse
    {
        $this->authorize('chat', $order);

        $validated = $request->validate([
            'message' => ['required_without:file', 'nullable', 'string', 'max:5000'],
            'file' => ['required_without:message', 'nullable', 'file', 'max:10240'],
        ]);

        $type = ChatMessageType::Text;
        $filePath = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $type = ChatMessageType::File;
            $file = $request->file('file');
            $filePath = $file->store('chat-files/' . $order->id, 'public');
            $fileName = $file->getClientOriginalName();
        }

        $message = ChatMessage::create([
            'order_id' => $order->id,
            'sender_id' => auth()->id(),
            'message' => $validated['message'] ?? null,
            'type' => $type,
            'file_path' => $filePath,
            'file_name' => $fileName,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->load('sender'),
        ]);
    }

    public function stream(Order $order): StreamedResponse
    {
        $this->authorize('chat', $order);

        return response()->stream(function () use ($order) {
            $lastId = request()->query('last_id', 0);
            $timeout = 0;
            $maxTimeout = 30;

            while ($timeout < $maxTimeout) {
                $messages = ChatMessage::with('sender')
                    ->where('order_id', $order->id)
                    ->where('id', '>', $lastId)
                    ->orderBy('id')
                    ->get();

                if ($messages->isNotEmpty()) {
                    $lastId = $messages->last()->id;

                    foreach ($messages as $message) {
                        if ($message->sender_id !== auth()->id()) {
                            $message->markAsRead();
                        }

                        $data = [
                            'id' => $message->id,
                            'sender_id' => $message->sender_id,
                            'sender_name' => $message->sender->name,
                            'message' => $message->message,
                            'type' => $message->type->value,
                            'file_path' => $message->file_path,
                            'file_name' => $message->file_name,
                            'file_url' => $message->file_url,
                            'created_at' => $message->created_at->toISOString(),
                            'is_own' => $message->sender_id === auth()->id(),
                        ];

                        echo "data: " . json_encode($data) . "\n\n";
                        ob_flush();
                        flush();
                    }
                }

                echo ": heartbeat\n\n";
                ob_flush();
                flush();

                if (connection_aborted()) {
                    break;
                }

                sleep(2);
                $timeout += 2;
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
