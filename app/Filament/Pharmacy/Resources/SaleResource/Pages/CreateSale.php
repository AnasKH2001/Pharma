<?php

namespace App\Filament\Pharmacy\Resources\SaleResource\Pages;

use App\Filament\Pharmacy\Concerns\HasCurrentPharmacy;
use App\Filament\Pharmacy\Resources\SaleResource;
use App\Services\SaleService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    use HasCurrentPharmacy;

    protected static string $resource = SaleResource::class;

    // Override the default Eloquent create — use SaleService instead
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $pharmacy = self::getCurrentPharmacy();

        try {
            $result = app(SaleService::class)->recordSale(
                $pharmacy->id,
                $data['medicine_id'],
                $data['quantity']
            );

            return $result['sale'];
        } catch (\Exception $e) {
            Notification::make()
                ->title('Could not record sale')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Sale recorded successfully')
            ->success();
    }
}
