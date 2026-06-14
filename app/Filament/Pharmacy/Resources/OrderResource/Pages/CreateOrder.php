<?php

namespace App\Filament\Pharmacy\Resources\OrderResource\Pages;

use App\Filament\Pharmacy\Concerns\HasCurrentPharmacy;
use App\Filament\Pharmacy\Resources\OrderResource;
use App\Services\OrderService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    use HasCurrentPharmacy;

    protected static string $resource = OrderResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $pharmacy = self::getCurrentPharmacy();

        $order = app(OrderService::class)->createOrder($pharmacy->id, $data['items']);

        return $order;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Order created — waiting for supplier offers')
            ->success();
    }
}
