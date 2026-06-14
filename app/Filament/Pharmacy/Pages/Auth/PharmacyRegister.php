<?php

namespace App\Filament\Pharmacy\Pages\Auth;

use App\Services\PharmacyService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;

class PharmacyRegister extends BaseRegister
{
    protected static string $view = 'filament.pharmacy.pages.auth.pharmacy-register';
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Pharmacy Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique('users', 'email')
                    ->maxLength(255),

                TextInput::make('password')
                    ->password()
                    ->required()
                    ->minLength(6)
                    ->same('password_confirmation'),

                TextInput::make('password_confirmation')
                    ->password()
                    ->required()
                    ->minLength(6)
                    ->dehydrated(false),

                TextInput::make('phone')
                    ->required()
                    ->tel(),

                Textarea::make('address')
                    ->required()
                    ->rows(2),

                TextInput::make('latitude')
                    ->required()
                    ->numeric()
                    ->minValue(-90)
                    ->maxValue(90),

                TextInput::make('longitude')
                    ->required()
                    ->numeric()
                    ->minValue(-180)
                    ->maxValue(180),

                TimePicker::make('opens_at')
                    ->required()
                    ->seconds(false),

                TimePicker::make('closes_at')
                    ->required()
                    ->seconds(false),

                FileUpload::make('credentials')
                    ->label('Credentials (license, ID, etc.)')
                    ->multiple()
                    ->required()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                    ->maxSize(10240)
                    ->disk('public')
                    ->directory('temp_credentials')
                    ->storeFiles(false), // we'll handle storage ourselves
            ]);
    }


    public function register(): ?RegistrationResponse
    {
        $data = $this->form->getState();

        $credentialFiles = $data['credentials'];

        app(PharmacyService::class)->register(
            [
                'name'      => $data['name'],
                'email'     => $data['email'],
                'password'  => $data['password'],
                'phone'     => $data['phone'],
                'address'   => $data['address'],
                'latitude'  => $data['latitude'],
                'longitude' => $data['longitude'],
                'opens_at'  => $data['opens_at'],
                'closes_at' => $data['closes_at'],
            ],
            $credentialFiles
        );

        // Do NOT log the user in — they need to verify OTP and wait for admin approval first

        $this->redirect(
            route('filament.pharmacy.auth.verify-otp', ['email' => $data['email']])
        );

        return null;
    }



    public function getTitle(): string
    {
        return 'Register your Pharmacy';
    }
}
