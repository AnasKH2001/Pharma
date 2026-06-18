<?php

namespace App\Filament\Resources\PharmacyResource\Pages;

use App\Filament\Resources\PharmacyResource;
use Dotswan\MapPicker\Infolists\MapEntry;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewPharmacy extends ViewRecord
{
    protected static string $resource = PharmacyResource::class;

    public function infolist(Infolist $infolist): Infolist
    {$pharmacy = $this->record;
        return $infolist
            ->schema([
                Components\Section::make('Details')
                    ->columns(2)
                    ->schema([
                        Components\TextEntry::make('name'),
                        Components\TextEntry::make('email'),
                        Components\TextEntry::make('phone'),
                        Components\TextEntry::make('address')
                            ->columnSpanFull(),
                        Components\TextEntry::make('opens_at')->time('H:i'),
                        Components\TextEntry::make('closes_at')->time('H:i'),
                        Components\IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean(),
                        Components\TextEntry::make('created_at')->date(),
                    ]),

                Components\Section::make('Location')
                    ->columns(2)
                    ->schema([
                        Components\TextEntry::make('latitude')
                            ->label('Latitude')
                            ->state(fn ($record) => number_format((float) $record->latitude, 6)),

                        Components\TextEntry::make('longitude')
                            ->label('Longitude')
                            ->state(fn ($record) => number_format((float) $record->longitude, 6)),

                        MapEntry::make('location')
                            ->label('')
                            ->state(fn ($record) => [
                                'lat' => (float) $record->latitude,
                                'lng' => (float) $record->longitude,
                            ])
                            ->zoom(14)
                            ->columnSpanFull(),
                    ]),

                Components\Section::make('Credentials')
                    ->schema([
                        Components\RepeatableEntry::make('credentialFiles')
                            ->label('')
                            ->schema([
                                Components\TextEntry::make('name')
                                    ->label('File')
                                    ->formatStateUsing(function ($state) use ($pharmacy) {
                                        // Build the file URL using the pharmacy's credentials folder and the filename
                                        $filePath = $pharmacy->credentials . '/' . $state;
                                        $url = Storage::disk('public')->url($filePath);

                                        return sprintf(
                                            '<a href="%s" target="_blank" class="text-primary-600 hover:underline">%s</a>',
                                            $url,
                                            $state
                                        );
                                    })
                                    ->html()
                                    ->icon('heroicon-o-paper-clip')
                                    ->iconColor('primary'),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }

}
