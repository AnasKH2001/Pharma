<?php

namespace App\Filament\Pharmacy\Pages\Auth;

use App\Services\UserService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;


class RequestPasswordReset extends BaseRequestPasswordReset
{
    protected static string $view = 'filament.pharmacy.pages.auth.request-password-reset';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->autofocus(),
            ]);
    }

    public function request(): void
    {
        $data = $this->form->getState();

        $result = app(UserService::class)->forgotPassword($data['email']);

        if (!$result['success']) {
            \Filament\Notifications\Notification::make()
                ->title($result['message'])
                ->danger()
                ->send();

            return;
        }

        \Filament\Notifications\Notification::make()
            ->title('OTP sent to your email')
            ->success()
            ->send();

        $this->redirect(
            route('filament.pharmacy.auth.reset-password-otp', ['email' => $data['email']])
        );
    }

    public function getTitle(): string
    {
        return 'Forgot Password';
    }
}
