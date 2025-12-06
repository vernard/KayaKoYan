<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case GCash = 'gcash';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match($this) {
            self::GCash => 'GCash',
            self::BankTransfer => 'Bank Transfer',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::GCash => 'info',
            self::BankTransfer => 'primary',
        };
    }
}
