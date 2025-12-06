<?php

namespace App\Filament\Worker\Resources\OrderResource\Pages;

use App\Filament\Worker\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
