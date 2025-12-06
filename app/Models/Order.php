<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderStatusTransitionException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'listing_id',
        'customer_id',
        'worker_id',
        'quantity',
        'unit_price',
        'total_price',
        'status',
        'notes',
        'delivered_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'status' => OrderStatus::class,
            'delivered_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'KKY';
        $date = now()->format('ymd');
        $random = strtoupper(substr(uniqid(), -4));

        return "{$prefix}-{$date}-{$random}";
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }

    public function digitalDownloads(): HasMany
    {
        return $this->hasMany(DigitalDownload::class);
    }

    public function getLatestPaymentAttribute(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    public function isDigitalProductOrder(): bool
    {
        return $this->listing->isDigitalProduct();
    }

    public function isServiceOrder(): bool
    {
        return $this->listing->isService();
    }

    public function canDownloadDigitalProduct(): bool
    {
        if (!$this->isDigitalProductOrder()) {
            return false;
        }

        return in_array($this->status, [
            OrderStatus::PaymentReceived,
            OrderStatus::Completed,
        ]);
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function transitionTo(OrderStatus $newStatus): void
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new InvalidOrderStatusTransitionException(
                "Cannot transition from {$this->status->value} to {$newStatus->value}"
            );
        }

        $this->status = $newStatus;

        match($newStatus) {
            OrderStatus::Delivered => $this->delivered_at = now(),
            OrderStatus::Completed => $this->completed_at = now(),
            default => null,
        };

        $this->save();
    }

    public function markPaymentReceived(): void
    {
        $this->transitionTo(OrderStatus::PaymentReceived);

        if ($this->isDigitalProductOrder()) {
            $this->transitionTo(OrderStatus::Completed);
        }
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForWorker($query, int $workerId)
    {
        return $query->where('worker_id', $workerId);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [OrderStatus::Completed, OrderStatus::Cancelled]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', OrderStatus::Completed);
    }

    /**
     * Check if chat is enabled for this order.
     * Chat is disabled for completed or cancelled orders.
     */
    public function isChatEnabled(): bool
    {
        return !in_array($this->status, [
            OrderStatus::Completed,
            OrderStatus::Cancelled,
        ]);
    }

    /**
     * Get the other participant in the chat.
     * Returns the worker if user is customer, or customer if user is worker.
     */
    public function getOtherParticipant(User $user): User
    {
        return $user->id === $this->customer_id
            ? $this->worker
            : $this->customer;
    }
}
