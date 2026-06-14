<?php

namespace App\Filament\Pharmacy\Resources\OrderResource\Pages;

use App\Filament\Pharmacy\Concerns\HasCurrentPharmacy;
use App\Filament\Pharmacy\Resources\OrderResource;
use App\Models\OrderOffer;
use App\Services\OrderService;
use Filament\Actions;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    use HasCurrentPharmacy;

    protected static string $resource = OrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Components\Section::make('Order Info')
                ->columns(3)
                ->schema([
                    Components\TextEntry::make('id')->label('Order #'),
                    Components\TextEntry::make('order_date')->date(),
                    Components\TextEntry::make('status')->badge()
                        ->color(fn ($state) => match($state) {
                            'pending'   => 'warning',
                            'assigned'  => 'success',
                            'cancelled' => 'danger',
                            default     => 'gray',
                        }),
                ]),

            Components\Section::make('Items Requested')
                ->schema([
                    Components\RepeatableEntry::make('orderItems')
                        ->label('')
                        ->schema([
                            Components\TextEntry::make('medicine.brand_name')->label('Medicine'),
                            Components\TextEntry::make('medicine.dosage')->label('Dosage'),
                            Components\TextEntry::make('quantity'),
                        ])
                        ->columns(3),
                ]),

            Components\Section::make('Supplier Offers')
                ->schema([
                    Components\RepeatableEntry::make('orderOffers')
                        ->label('')
                        ->schema([
                            Components\TextEntry::make('supplier.name')->label('Supplier'),
                            Components\TextEntry::make('description')->label('Notes'),
                            Components\TextEntry::make('status')->badge()
                                ->color(fn ($state) => match($state) {
                                    'pending'   => 'warning',
                                    'accepted'  => 'success',
                                    'rejected'  => 'danger',
                                    'cancelled' => 'gray',
                                    default     => 'gray',
                                }),
                            Components\RepeatableEntry::make('itemOffers')
                                ->label('Item Prices')
                                ->schema([
                                    Components\TextEntry::make('orderItem.medicine.brand_name')->label('Medicine'),
                                    Components\TextEntry::make('price')->money('usd'),
                                ])
                                ->columns(2),
                        ])
                        ->columns(1),
                ])
                ->visible(fn ($record) => $record->orderOffers->isNotEmpty()),
        ]);
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Build accept/reject action per pending offer
        foreach ($this->record->orderOffers as $offer) {
            if ($offer->status !== 'pending') {
                continue;
            }

            $actions[] = Actions\Action::make("accept_{$offer->id}")
                ->label("Accept offer from {$offer->supplier->name}")
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($offer) {
                    app(OrderService::class)->acceptOffer($offer->id);

                    Notification::make()
                        ->title('Offer accepted — order assigned')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                });

            $actions[] = Actions\Action::make("reject_{$offer->id}")
                ->label("Reject offer from {$offer->supplier->name}")
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () use ($offer) {
                    app(OrderService::class)->rejectOffer($offer->id);

                    Notification::make()
                        ->title('Offer rejected')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                });
        }

        return $actions;
    }
}
