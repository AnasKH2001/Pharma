<?php

namespace App\Filament\Pharmacy\Resources\OrderResource\Pages;

use App\Filament\Pharmacy\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('New Order')];
    }
}
