<?php

namespace App\Filament\Worker\Resources\ListingResource\Pages;

use App\Filament\Worker\Resources\ListingResource;
use App\Models\ListingImage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditListing extends EditRecord
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['images'] = $this->record->images->pluck('path')->toArray();
        return $data;
    }

    protected function afterSave(): void
    {
        $listing = $this->record;
        $images = $this->data['images'] ?? [];

        $listing->images()->delete();

        foreach ($images as $index => $imagePath) {
            ListingImage::create([
                'listing_id' => $listing->id,
                'path' => $imagePath,
                'order' => $index,
            ]);
        }
    }
}
