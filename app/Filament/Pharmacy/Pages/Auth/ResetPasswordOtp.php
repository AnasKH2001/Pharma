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

class ResetPasswordOtp extends SimplePage implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pharmacy.pages.auth.reset-password-otp';

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

                TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->required()
                    ->minLength(6)
                    ->same('password_confirmation'),

                TextInput::make('password_confirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->required()
                    ->minLength(6)
                    ->dehydrated(false),
            ])
            ->statePath('data');
    }

    public function resetPassword(): void
    {
        $data = $this->form->getState();

        $result = app(UserService::class)->resetPassword(
            $data['email'],
            $data['otp'],
            $data['password']
        );

        if (!$result['success']) {
            Notification::make()
                ->title($result['message'])
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title('Password reset successfully. You can now log in.')
            ->success()
            ->send();

        $this->redirect(route('filament.pharmacy.auth.login'));
    }

    public function getHeading(): string|Htmlable
    {
        return 'Reset your password';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Reset Password';
    }
}
