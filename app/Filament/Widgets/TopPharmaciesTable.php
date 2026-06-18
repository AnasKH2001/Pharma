<?php

namespace App\Filament\Widgets;

use App\Services\StatService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class TopPharmaciesTable extends BaseWidget
{
    protected static ?string $heading = 'Top Pharmacies by Revenue';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\PharmaSale::query()
                    ->selectRaw('pharmacy_id as id, pharmacy_id, SUM(quantity) as total_items_sold, SUM(total_price) as total_revenue, COUNT(*) as total_sales')
                    ->groupBy('pharmacy_id')
                    ->orderBy('total_revenue', 'desc')
                    ->limit(10)
                    ->with('pharmacy')
            )
            ->columns([
                Tables\Columns\TextColumn::make('pharmacy.name')
                    ->label('Pharmacy')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Total Sales')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_items_sold')
                    ->label('Items Sold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('pharmacy.is_active')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ]);
    }
}
