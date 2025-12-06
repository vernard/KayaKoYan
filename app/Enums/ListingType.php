<?php

namespace App\Enums;

enum ListingType: string
{
    case Service = 'service';
    case DigitalProduct = 'digital_product';

    public function label(): string
    {
        return match($this) {
            self::Service => 'Service',
            self::DigitalProduct => 'Digital Product',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Service => 'primary',
            self::DigitalProduct => 'success',
        };
    }
}
