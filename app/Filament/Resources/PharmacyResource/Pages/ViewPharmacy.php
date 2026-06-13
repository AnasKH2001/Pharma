<?php

namespace App\Filament\Resources\PharmacyResource\Pages;

use App\Filament\Resources\PharmacyResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ViewPharmacy extends ViewRecord
{
    protected static string $resource = PharmacyResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Details')
                    ->columns(2)
                    ->schema([
                        Components\TextEntry::make('name'),
                        Components\TextEntry::make('email'),
                        Components\TextEntry::make('phone'),
                        Components\TextEntry::make('address'),
                        Components\TextEntry::make('opens_at')->time('H:i'),
                        Components\TextEntry::make('closes_at')->time('H:i'),
                        Components\IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean(),
                        Components\TextEntry::make('created_at')->date(),
                    ]),

                Components\Section::make('Credentials')
                    ->schema([
                        Components\RepeatableEntry::make('credentialFiles')
                            ->label('')
                            ->schema([
                                Components\TextEntry::make('name')->label('File'),
                                Components\TextEntry::make('url')
                                    ->label('Link')
                                    ->url(fn ($state) => $state)
                                    ->openUrlInNewTab(),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    // Virtual attribute to feed the RepeatableEntry
    public function getCredentialFilesAttribute(): array
    {
        return app(\App\Repositories\AdminRepository::class)
            ->getPharmacyCredentials($this->record);
    }
}
