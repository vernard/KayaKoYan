<?php

namespace App\Models;

use App\Enums\ChatMessageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'sender_id',
        'message',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ChatMessageType::class,
            'read_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isText(): bool
    {
        return $this->type === ChatMessageType::Text;
    }

    public function isFile(): bool
    {
        return $this->type === ChatMessageType::File;
    }

    public function isDeliveryNotice(): bool
    {
        return $this->type === ChatMessageType::DeliveryNotice;
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return asset('storage/' . $this->file_path);
    }

    public function isImage(): bool
    {
        if (!$this->file_name) {
            return false;
        }

        $extension = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    }

    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 1) . ' ' . $units[$index];
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }
}
