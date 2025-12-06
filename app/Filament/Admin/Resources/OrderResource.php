<?php

namespace App\Filament\Admin\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 3;

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Details')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('order_number')
                            ->label('Order #'),
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (OrderStatus $state): string => $state->color()),
                        \Filament\Infolists\Components\TextEntry::make('listing.title')
                            ->label('Listing'),
                        \Filament\Infolists\Components\TextEntry::make('total_price')
                            ->money('PHP'),
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Participants')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('customer.name')
                            ->label('Customer'),
                        \Filament\Infolists\Components\TextEntry::make('customer.email')
                            ->label('Customer Email'),
                        \Filament\Infolists\Components\TextEntry::make('worker.name')
                            ->label('Worker'),
                        \Filament\Infolists\Components\TextEntry::make('worker.email')
                            ->label('Worker Email'),
                    ])
                    ->columns(2),

                Section::make('Payment')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('latestPayment.method')
                            ->label('Method')
                            ->badge(),
                        \Filament\Infolists\Components\TextEntry::make('latestPayment.reference_number')
                            ->label('Reference #'),
                        \Filament\Infolists\Components\TextEntry::make('latestPayment.status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (?PaymentStatus $state): string => $state?->color() ?? 'gray'),
                    ])
                    ->columns(3)
                    ->visible(fn (Order $record): bool => $record->payments()->exists()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable(),

                Tables\Columns\TextColumn::make('listing.title')
                    ->label('Listing')
                    ->limit(30),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('worker.name')
                    ->label('Worker')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->money('PHP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (OrderStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(OrderStatus::class),
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
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
