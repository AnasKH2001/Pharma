<?php

namespace App\Filament\Pharmacy\Widgets;

use App\Filament\Pharmacy\Concerns\HasCurrentPharmacy;
use App\Services\StatService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    use HasCurrentPharmacy;

    protected static ?string $heading = 'Low Stock Alert';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $pharmacy = self::getCurrentPharmacy();

        return $table
            ->query(
                \App\Models\Inventory::with('medicine')
                    ->where('pharmacy_id', $pharmacy->id)
                    ->where('quantity', '<', 10)
                    ->orderBy('quantity', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('medicine.brand_name')
                    ->label('Medicine')
                    ->searchable(),

                Tables\Columns\TextColumn::make('medicine.generic_name')
                    ->label('Generic'),

                Tables\Columns\TextColumn::make('medicine.dosage')
                    ->label('Dosage'),

                Tables\Columns\TextColumn::make('quantity')
                    ->badge()
                    ->color(fn ($state) => $state === 0 ? 'danger' : 'warning'),

                Tables\Columns\TextColumn::make('price')
                    ->money('usd'),
            ]);
    }
}
