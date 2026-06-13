<?php

namespace App\Filament\Resources\PharmacyResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';
    protected static ?string $title = 'Inventory';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('medicine.brand_name')
                    ->label('Brand Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('medicine.generic_name')
                    ->label('Generic Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('medicine.dosage')
                    ->label('Dosage'),

                Tables\Columns\TextColumn::make('medicine.form')
                    ->label('Form'),

                Tables\Columns\TextColumn::make('quantity')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state === 0    => 'danger',
                        $state < 10     => 'warning',
                        default         => 'success',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->money('usd')
                    ->sortable(),
            ])
            ->defaultSort('quantity', 'asc') // low stock floats to top
            ->filters([
                Tables\Filters\SelectFilter::make('stock_status')
                    ->label('Stock Status')
                    ->options([
                        'out'  => 'Out of Stock',
                        'low'  => 'Low Stock (< 10)',
                        'ok'   => 'In Stock',
                    ])
                    ->query(fn ($query, $data) => match($data['value'] ?? null) {
                        'out' => $query->where('quantity', 0),
                        'low' => $query->where('quantity', '>', 0)->where('quantity', '<', 10),
                        'ok'  => $query->where('quantity', '>=', 10),
                        default => $query,
                    }),
            ]);
    }
}
