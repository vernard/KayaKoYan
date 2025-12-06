<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case PaymentSubmitted = 'payment_submitted';
    case PaymentReceived = 'payment_received';
    case InProgress = 'in_progress';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PendingPayment => 'Pending Payment',
            self::PaymentSubmitted => 'Payment Submitted',
            self::PaymentReceived => 'Payment Received',
            self::InProgress => 'In Progress',
            self::Delivered => 'Delivered',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PendingPayment => 'gray',
            self::PaymentSubmitted => 'warning',
            self::PaymentReceived => 'info',
            self::InProgress => 'primary',
            self::Delivered => 'success',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function allowedTransitions(): array
    {
        return match($this) {
            self::PendingPayment => [self::PaymentSubmitted, self::Cancelled],
            self::PaymentSubmitted => [self::PaymentReceived, self::Cancelled],
            self::PaymentReceived => [self::InProgress, self::Completed],
            self::InProgress => [self::Delivered],
            self::Delivered => [self::Completed],
            self::Completed => [],
            self::Cancelled => [],
        };
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions());
    }

    public function isActive(): bool
    {
        return in_array($this, [
            self::PendingPayment,
            self::PaymentSubmitted,
            self::PaymentReceived,
            self::InProgress,
            self::Delivered,
        ]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled]);
    }
}
