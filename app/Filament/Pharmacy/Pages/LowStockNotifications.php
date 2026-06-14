<?php

namespace App\Filament\Pharmacy\Pages;

use App\Filament\Pharmacy\Concerns\HasCurrentPharmacy;
use App\Models\LowStockNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class LowStockNotifications extends Page implements HasTable
{
    use HasCurrentPharmacy;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Low Stock Alerts';
    protected static ?string $title = 'Low Stock Notifications';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pharmacy.pages.low-stock-notifications';

    public static function getNavigationBadge(): ?string
    {
        $pharmacy = self::getCurrentPharmacy();

        $count = LowStockNotification::where('pharmacy_id', $pharmacy->id)
            ->where('is_read', false)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function table(Table $table): Table
    {
        $pharmacy = self::getCurrentPharmacy();

        return $table
            ->query(
                LowStockNotification::where('pharmacy_id', $pharmacy->id)
                    ->with('medicine')
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\IconColumn::make('is_read')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('medicine.brand_name')
                    ->label('Medicine')
                    ->weight(fn ($record) => $record->is_read ? 'normal' : 'bold'),

                Tables\Columns\TextColumn::make('medicine.generic_name')
                    ->label('Generic'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('Read status')
                    ->trueLabel('Read')
                    ->falseLabel('Unread')
                    ->placeholder('All'),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_read')
                    ->label('Mark as read')
                    ->icon('heroicon-o-check')
                    ->color('gray')
                    ->visible(fn (LowStockNotification $record) => !$record->is_read)
                    ->action(function (LowStockNotification $record) {
                        $record->update(['is_read' => true]);
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('mark_all_read')
                    ->label('Mark all as read')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function () {
                        $pharmacy = self::getCurrentPharmacy();

                        LowStockNotification::where('pharmacy_id', $pharmacy->id)
                            ->where('is_read', false)
                            ->update(['is_read' => true]);

                        Notification::make()
                            ->title('All notifications marked as read')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
