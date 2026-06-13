<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PharmacyResource\Pages;
use App\Models\Pharmacy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use App\Filament\Resources\PharmacyResource\RelationManagers;

class PharmacyResource extends Resource
{
    protected static ?string $model = Pharmacy::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Pharmacies';
    protected static ?int $navigationSort = 1;

    // Badge showing pending count on the sidebar
    public static function getNavigationBadge(): ?string
    {
        $pending = Pharmacy::where('is_active', false)->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pharmacy Info')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('email')->email()->required(),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\TextInput::make('address'),
                        Forms\Components\TimePicker::make('opens_at'),
                        Forms\Components\TimePicker::make('closes_at'),
                        Forms\Components\Toggle::make('is_active')->label('Approved'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone'),

                Tables\Columns\TextColumn::make('address')
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Approved' : 'Pending')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Approved',
                        '0' => 'Pending',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Pharmacy $record) => !$record->is_active)
                    ->requiresConfirmation()
                    ->action(function (Pharmacy $record) {
                        $record->update(['is_active' => true]);
                        Notification::make()
                            ->title('Pharmacy approved')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Pharmacy $record) => !$record->is_active)
                    ->requiresConfirmation()
                    ->modalDescription('This will permanently delete the pharmacy and its user account.')
                    ->action(function (Pharmacy $record) {
                        // Mirror your AdminRepository::rejectPharmacy logic
                        $user = \App\Models\User::where('email', $record->email)->first();
                        if ($user) $user->delete();
                        // pharmacy cascades or delete separately if needed
                        Notification::make()
                            ->title('Pharmacy rejected and deleted')
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            RelationManagers\InventoryRelationManager::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPharmacies::route('/'),
            'view'  => Pages\ViewPharmacy::route('/{record}'),
            'edit'  => Pages\EditPharmacy::route('/{record}/edit'),
        ];
    }
}
