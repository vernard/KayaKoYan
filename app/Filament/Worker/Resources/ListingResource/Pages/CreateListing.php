<?php

namespace App\Filament\Worker\Resources\ListingResource\Pages;

use App\Filament\Worker\Resources\ListingResource;
use App\Models\ListingImage;
use Filament\Resources\Pages\CreateRecord;

class CreateListing extends CreateRecord
{
    protected static string $resource = ListingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        $listing = $this->record;
        $images = $this->data['images'] ?? [];

        foreach ($images as $index => $imagePath) {
            ListingImage::create([
                'listing_id' => $listing->id,
                'path' => $imagePath,
                'order' => $index,
            ]);
        }
    }
}
