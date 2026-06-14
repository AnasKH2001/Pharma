<?php

namespace App\Filament\Pharmacy\Pages\Auth;

use App\Services\UserService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Support\Htmlable;

class VerifyOtp extends SimplePage implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pharmacy.pages.auth.verify-otp';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'email' => request()->query('email'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->readOnly(),

                TextInput::make('otp')
                    ->label('OTP Code')
                    ->required()
                    ->length(6)
                    ->numeric(),
            ])
            ->statePath('data');
    }

    public function verify(): void
    {
        $data = $this->form->getState();

        $result = app(UserService::class)->verifyOtp($data['email'], $data['otp']);

        if (!$result['success']) {
            Notification::make()
                ->title($result['message'])
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title('Email verified! You can now log in once approved.')
            ->success()
            ->send();

        $this->redirect(route('filament.pharmacy.auth.login'));
    }

    public function resend(): void
    {
        $email = $this->form->getState()['email'] ?? null;

        if (!$email) {
            return;
        }

        $result = app(UserService::class)->resendOtp($email);

        Notification::make()
            ->title($result['message'])
            ->color($result['success'] ? 'success' : 'danger')
            ->send();
    }

    public function getHeading(): string|Htmlable
    {
        return 'Verify your email';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Verify OTP';
    }
}
