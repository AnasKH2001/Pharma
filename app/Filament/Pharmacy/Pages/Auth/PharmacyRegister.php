<?php

namespace App\Filament\Pharmacy\Pages\Auth;

use App\Services\PharmacyService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Dotswan\MapPicker\Fields\Map;

class PharmacyRegister extends BaseRegister
{
    protected static string $view = 'filament.pharmacy.pages.auth.pharmacy-register';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Account Details')
                    ->description('Login credentials for your pharmacy account')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Pharmacy Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique('users', 'email')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('password')
                            ->password()
                            ->required()
                            ->minLength(6)
                            ->same('password_confirmation')
                            ->revealable(),

                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->required()
                            ->minLength(6)
                            ->dehydrated(false)
                            ->revealable(),
                    ]),

                Section::make('Contact & Hours')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')
                            ->required()
                            ->tel(),

                        Textarea::make('address')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),

                        TimePicker::make('opens_at')
                            ->required()
                            ->seconds(false),

                        TimePicker::make('closes_at')
                            ->required()
                            ->seconds(false),
                    ]),

                Section::make('Location')
                    ->description('Click on the map or drag the marker to set your pharmacy location')
                    ->schema([
                        Map::make('location')
                            ->label('')
                            ->defaultLocation(latitude: 33.5138, longitude: 36.2765) // Damascus default
                            ->draggable()
                            ->clickable(true)
                            ->zoom(13)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (is_array($state)) {
                                    $set('latitude', $state['lat'] ?? null);
                                    $set('longitude', $state['lng'] ?? null);
                                }
                            })
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('latitude')
                            ->required()
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90)
                            ->readOnly(),

                        TextInput::make('longitude')
                            ->required()
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180)
                            ->readOnly(),
                    ])
                    ->columns(2),

                Section::make('Credentials')
                    ->description('Upload your pharmacy license, ID, or other verification documents')
                    ->schema([
                        FileUpload::make('credentials')
                            ->label('Credentials (license, ID, etc.)')
                            ->multiple()
                            ->required()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(10240)
                            ->disk('public')
                            ->directory('temp_credentials')
                            ->storeFiles(false)
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
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
