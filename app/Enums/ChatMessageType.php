<?php

namespace App\Enums;

enum ChatMessageType: string
{
    case Text = 'text';
    case File = 'file';
    case DeliveryNotice = 'delivery_notice';

    public function label(): string
    {
        return match($this) {
            self::Text => 'Text',
            self::File => 'File',
            self::DeliveryNotice => 'Delivery Notice',
        };
    }
}
