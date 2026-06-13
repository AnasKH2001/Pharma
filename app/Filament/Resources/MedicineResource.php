<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicineResource\Pages;
use App\Imports\MedicinesImport;
use App\Models\Medicine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'Medicines';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('brand_name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('generic_name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('manufacturer')
                    ->maxLength(255),

                Forms\Components\TextInput::make('dosage')
                    ->maxLength(100),

                Forms\Components\TextInput::make('form')
                    ->label('Form (tablet, syrup…)')
                    ->maxLength(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brand_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('generic_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('manufacturer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('dosage'),

                Tables\Columns\TextColumn::make('form'),

                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
            ])
            ->headerActions([
                // Excel upload action on the table header
                Tables\Actions\Action::make('upload_excel')
                    ->label('Upload Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('Excel File')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $import = new MedicinesImport();
                        Excel::import($import, storage_path('app/public/' . $data['file']));

                        $added   = $import->getAddedCount();
                        $skipped = $import->getSkippedCount();

                        Notification::make()
                            ->title("{$added} medicines added, {$skipped} duplicates skipped")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMedicines::route('/'),
            'create' => Pages\CreateMedicine::route('/create'),
            'edit'   => Pages\EditMedicine::route('/{record}/edit'),
        ];
    }
}
