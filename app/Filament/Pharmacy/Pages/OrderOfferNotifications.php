<?php

namespace App\Filament\Pharmacy\Pages;

use App\Filament\Pharmacy\Concerns\HasCurrentPharmacy;
use App\Filament\Pharmacy\Resources\OrderResource;
use App\Models\OrderOfferNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class OrderOfferNotifications extends Page implements HasTable
{
    use HasCurrentPharmacy;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationLabel = 'Offer Notifications';
    protected static ?string $title = 'Order Offer Notifications';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pharmacy.pages.order-offer-notifications';

    public static function getNavigationBadge(): ?string
    {
        $pharmacy = self::getCurrentPharmacy();

        $count = OrderOfferNotification::where('pharmacy_id', $pharmacy->id)
            ->where('is_read', false)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function table(Table $table): Table
    {
        $pharmacy = self::getCurrentPharmacy();

        return $table
            ->query(
                OrderOfferNotification::where('pharmacy_id', $pharmacy->id)
                    ->with('orderOffer.order', 'orderOffer.supplier')
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\IconColumn::make('is_read')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('orderOffer.order.id')
                    ->label('Order #')
                    ->weight(fn ($record) => $record->is_read ? 'normal' : 'bold'),

                Tables\Columns\TextColumn::make('orderOffer.supplier.name')
                    ->label('Supplier'),

                Tables\Columns\BadgeColumn::make('orderOffer.status')
                    ->label('Offer Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'accepted',
                        'danger'  => 'rejected',
                        'gray'    => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Offer status')
                    ->relationship('orderOffer', 'status')
                    ->options([
                        'pending'   => 'Pending',
                        'accepted'  => 'Accepted',
                        'rejected'  => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('Read status')
                    ->trueLabel('Read')
                    ->falseLabel('Unread')
                    ->placeholder('All'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_order')
                    ->label('View Order')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (OrderOfferNotification $record) => OrderResource::getUrl('view', [
                        'record' => $record->orderOffer->order_id,
                    ]))
                    ->action(function (OrderOfferNotification $record) {
                        // mark as read when opening
                        $record->update(['is_read' => true]);
                    }),

                Tables\Actions\Action::make('mark_read')
                    ->label('Mark as read')
                    ->icon('heroicon-o-check')
                    ->color('gray')
                    ->visible(fn (OrderOfferNotification $record) => !$record->is_read)
                    ->action(function (OrderOfferNotification $record) {
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

                        OrderOfferNotification::where('pharmacy_id', $pharmacy->id)
                            ->where('is_read', false)
                            ->update(['is_read' => true]);

                        Notification::make()
                            ->title('All offer notifications marked as read')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
