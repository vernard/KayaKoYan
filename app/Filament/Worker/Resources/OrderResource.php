<?php

namespace App\Filament\Worker\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Worker\Resources\OrderResource\Pages;
use App\Models\Delivery;
use App\Models\Order;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('worker_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

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
                            ->label('Service'),
                        \Filament\Infolists\Components\TextEntry::make('total_price')
                            ->money('PHP'),
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Customer')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('customer.name'),
                        \Filament\Infolists\Components\TextEntry::make('customer.email'),
                        \Filament\Infolists\Components\TextEntry::make('notes')
                            ->label('Customer Notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Payment')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('latestPayment.method')
                            ->label('Payment Method')
                            ->badge(),
                        \Filament\Infolists\Components\TextEntry::make('latestPayment.reference_number')
                            ->label('Reference #'),
                        \Filament\Infolists\Components\TextEntry::make('latestPayment.status')
                            ->label('Payment Status')
                            ->badge()
                            ->color(fn (?PaymentStatus $state): string => $state?->color() ?? 'gray'),
                        \Filament\Infolists\Components\ImageEntry::make('latestPayment.proof_path')
                            ->label('Payment Proof')
                            ->disk('public')
                            ->columnSpanFull(),
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
                    ->label('Service')
                    ->limit(30),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer'),

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

                Action::make('verify_payment')
                    ->label('Verify Payment')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verify Payment')
                    ->modalDescription('Are you sure you have received the payment? This will allow you to start working on the order.')
                    ->visible(fn (Order $record): bool =>
                        $record->status === OrderStatus::PaymentSubmitted &&
                        $record->latestPayment?->status === PaymentStatus::Pending)
                    ->action(function (Order $record) {
                        $record->latestPayment->verify();
                        Notification::make()
                            ->title('Payment verified!')
                            ->success()
                            ->send();
                    }),

                Action::make('start_work')
                    ->label('Start Work')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool =>
                        $record->status === OrderStatus::PaymentReceived &&
                        $record->listing->isService())
                    ->action(function (Order $record) {
                        $record->transitionTo(OrderStatus::InProgress);
                        Notification::make()
                            ->title('Work started!')
                            ->success()
                            ->send();
                    }),

                Action::make('deliver')
                    ->label('Submit Delivery')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->form([
                        Textarea::make('notes')
                            ->label('Delivery Notes')
                            ->helperText('Describe what you have delivered')
                            ->required(),
                        FileUpload::make('files')
                            ->label('Delivery Files')
                            ->multiple()
                            ->maxFiles(10)
                            ->disk('public')
                            ->directory('delivery-files'),
                    ])
                    ->visible(fn (Order $record): bool =>
                        in_array($record->status, [OrderStatus::PaymentReceived, OrderStatus::InProgress]) &&
                        $record->listing->isService())
                    ->action(function (Order $record, array $data) {
                        $delivery = Delivery::create([
                            'order_id' => $record->id,
                            'notes' => $data['notes'],
                        ]);

                        foreach ($data['files'] ?? [] as $filePath) {
                            $delivery->files()->create([
                                'file_path' => $filePath,
                                'file_name' => basename($filePath),
                            ]);
                        }

                        Notification::make()
                            ->title('Delivery submitted!')
                            ->body('The customer has been notified.')
                            ->success()
                            ->send();
                    }),

                Action::make('chat')
                    ->label('Chat with Customer')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn (Order $record): string => route('chats.show', $record))
                    ->openUrlInNewTab(),
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

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()
            ->whereIn('status', [
                OrderStatus::PaymentSubmitted,
                OrderStatus::PaymentReceived,
                OrderStatus::InProgress,
            ])
            ->count() ?: null;
    }
}
