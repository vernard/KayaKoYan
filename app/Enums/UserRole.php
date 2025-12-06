<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Worker = 'worker';
    case Customer = 'customer';

    public function label(): string
    {
        return match($this) {
            self::Admin => 'Admin',
            self::Worker => 'Worker',
            self::Customer => 'Customer',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Admin => 'danger',
            self::Worker => 'success',
            self::Customer => 'info',
        };
    }
}
