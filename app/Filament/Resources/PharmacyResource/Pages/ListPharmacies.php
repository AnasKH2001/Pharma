<?php

namespace App\Filament\Resources\PharmacyResource\Pages;

use App\Filament\Resources\PharmacyResource;
use App\Models\Pharmacy;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPharmacies extends ListRecords
{
    protected static string $resource = PharmacyResource::class;

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(fn () => Pharmacy::where('is_active', false)->count())
                ->badgeColor('warning'),

            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => Pharmacy::where('is_active', true)->count())
                ->badgeColor('success'),

            'all' => Tab::make('All')
                ->badge(fn () => Pharmacy::count()),
        ];
    }
}
