<?php

namespace App\Filament\Worker\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Worker\Resources\OrderResource;
use App\Models\Delivery;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('verify_payment')
                ->label('Verify Payment')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verify Payment')
                ->modalDescription('Are you sure you have received the payment? This will allow you to start working on the order.')
                ->visible(fn (): bool =>
                    $this->record->status === OrderStatus::PaymentSubmitted &&
                    $this->record->latestPayment?->status === PaymentStatus::Pending)
                ->action(function () {
                    $this->record->latestPayment->verify();
                    Notification::make()
                        ->title('Payment verified!')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('start_work')
                ->label('Start Work')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn (): bool =>
                    $this->record->status === OrderStatus::PaymentReceived &&
                    $this->record->listing->isService())
                ->action(function () {
                    $this->record->transitionTo(OrderStatus::InProgress);
                    Notification::make()
                        ->title('Work started!')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('deliver')
                ->label('Submit Delivery')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->form([
                    Forms\Components\Textarea::make('notes')
                        ->label('Delivery Notes')
                        ->helperText('Describe what you have delivered')
                        ->required(),
                    Forms\Components\FileUpload::make('files')
                        ->label('Delivery Files')
                        ->multiple()
                        ->maxFiles(10)
                        ->disk('public')
                        ->directory('delivery-files'),
                ])
                ->visible(fn (): bool =>
                    in_array($this->record->status, [OrderStatus::PaymentReceived, OrderStatus::InProgress]) &&
                    $this->record->listing->isService())
                ->action(function (array $data) {
                    $delivery = Delivery::create([
                        'order_id' => $this->record->id,
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

            Actions\Action::make('chat')
                ->label('Chat with Customer')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->url(fn (): string => route('chats.show', $this->record))
                ->openUrlInNewTab(),
        ];
    }
}
