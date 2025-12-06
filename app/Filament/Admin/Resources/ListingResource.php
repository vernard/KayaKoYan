<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ListingType;
use App\Filament\Admin\Resources\ListingResource\Pages;
use App\Models\Listing;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Component;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Listing Details')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('title'),
                        \Filament\Infolists\Components\TextEntry::make('slug'),
                        \Filament\Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->color(fn (ListingType $state): string => $state->color()),
                        \Filament\Infolists\Components\TextEntry::make('price')
                            ->money('PHP'),
                        \Filament\Infolists\Components\IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Worker')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('user.name')
                            ->label('Name'),
                        \Filament\Infolists\Components\TextEntry::make('user.email')
                            ->label('Email'),
                    ])
                    ->columns(2),

                Section::make('Description')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('description')
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Worker')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (ListingType $state): string => $state->color()),

                Tables\Columns\TextColumn::make('price')
                    ->money('PHP')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(ListingType::class),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListings::route('/'),
            'view' => Pages\ViewListing::route('/{record}'),
        ];
    }
}
