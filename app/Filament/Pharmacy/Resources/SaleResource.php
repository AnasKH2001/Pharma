<?php

namespace App\Filament\Pharmacy\Resources;

use App\Filament\Pharmacy\Concerns\HasCurrentPharmacy;
use App\Filament\Pharmacy\Resources\SaleResource\Pages;
use App\Models\Inventory;
use App\Models\PharmaSale;
use App\Services\SaleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaleResource extends Resource
{
    use HasCurrentPharmacy;

    protected static ?string $model = PharmaSale::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Sales';
    protected static ?int $navigationSort = 2;

    // Scope to current pharmacy
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('pharmacy_id', self::getCurrentPharmacy()->id)
            ->with('medicine');
    }

    public static function form(Form $form): Form
    {
        $pharmacyId = self::getCurrentPharmacy()->id;

        return $form->schema([
            Forms\Components\Select::make('medicine_id')
                ->label('Medicine')
                ->options(function () use ($pharmacyId) {
                    return Inventory::where('pharmacy_id', $pharmacyId)
                        ->where('quantity', '>', 0)
                        ->with('medicine')
                        ->get()
                        ->mapWithKeys(function ($inv) {
                            $label = "{$inv->medicine->brand_name} ({$inv->medicine->dosage}) — Stock: {$inv->quantity} — \${$inv->price}";
                            return [$inv->medicine_id => $label];
                        });
                })
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) use ($pharmacyId) {
                    $inv = Inventory::where('pharmacy_id', $pharmacyId)
                        ->where('medicine_id', $state)
                        ->first();

                    $set('available_stock', $inv?->quantity ?? 0);
                    $set('unit_price', $inv?->price ?? 0);
                }),

            Forms\Components\Placeholder::make('available_stock')
                ->label('Available Stock')
                ->content(fn ($get) => $get('available_stock') ?? '-'),

            Forms\Components\Placeholder::make('unit_price')
                ->label('Unit Price')
                ->content(fn ($get) => $get('unit_price') ? '$' . $get('unit_price') : '-'),

            Forms\Components\TextInput::make('quantity')
                ->numeric()
                ->required()
                ->minValue(1)
                ->reactive(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('medicine.brand_name')
                    ->label('Medicine')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('medicine.generic_name')
                    ->label('Generic'),

                Tables\Columns\TextColumn::make('medicine.dosage')
                    ->label('Dosage'),

                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_price')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sold At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
        ];
    }
}
