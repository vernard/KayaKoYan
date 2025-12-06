<?php

namespace App\Filament\Worker\Pages;

use App\Models\WorkerProfile;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class EditProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'My Profile';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.worker.pages.edit-profile';

    public ?array $data = [];

    public function mount(): void
    {
        $profile = auth()->user()->workerProfile;

        $this->form->fill([
            'bio' => $profile?->bio,
            'phone' => $profile?->phone,
            'location' => $profile?->location,
            'gcash_number' => $profile?->gcash_number,
            'gcash_name' => $profile?->gcash_name,
            'bank_name' => $profile?->bank_name,
            'bank_account_number' => $profile?->bank_account_number,
            'bank_account_name' => $profile?->bank_account_name,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile Information')
                    ->description('This information will be visible on your public profile.')
                    ->schema([
                        Textarea::make('bio')
                            ->label('About Me')
                            ->helperText('Tell customers about yourself and your services.')
                            ->rows(4)
                            ->maxLength(1000),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('location')
                            ->maxLength(255)
                            ->helperText('e.g., Manila, Philippines'),
                    ]),

                Section::make('GCash Details')
                    ->description('Your GCash information for receiving payments.')
                    ->schema([
                        TextInput::make('gcash_number')
                            ->label('GCash Number')
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('gcash_name')
                            ->label('GCash Account Name')
                            ->maxLength(255)
                            ->helperText('The name registered to your GCash account.'),
                    ])
                    ->columns(2),

                Section::make('Bank Details')
                    ->description('Your bank account information for receiving payments.')
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->maxLength(255)
                            ->helperText('e.g., BDO, BPI, Metrobank'),

                        TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->maxLength(100),

                        TextInput::make('bank_account_name')
                            ->label('Account Name')
                            ->maxLength(255)
                            ->helperText('The name registered to your bank account.'),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $profile = auth()->user()->workerProfile;

        if (!$profile) {
            $profile = WorkerProfile::create([
                'user_id' => auth()->id(),
                ...$data,
            ]);
        } else {
            $profile->update($data);
        }

        Notification::make()
            ->title('Profile updated!')
            ->success()
            ->send();
    }
}
