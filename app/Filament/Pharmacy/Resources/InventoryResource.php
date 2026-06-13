<?php

namespace App\Filament\Pharmacy\Resources;

use App\Filament\Pharmacy\Concerns\HasCurrentPharmacy;
use App\Filament\Pharmacy\Resources\InventoryResource\Pages;
use App\Imports\InventoryImport;
use App\Models\Inventory;
use App\Repositories\InventoryRepository;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class InventoryResource extends Resource
{
    use HasCurrentPharmacy;

    protected static ?string $model = Inventory::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Inventory';
    protected static ?int $navigationSort = 1;

    // Scope all queries to current pharmacy
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('pharmacy_id', self::getCurrentPharmacy()->id)
            ->with('medicine');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);  // read-only resource, no manual create
    }

    public static function table(Table $table): Table
    {
        return $table
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
                        $state === 0 => 'danger',
                        $state < 10  => 'warning',
                        default      => 'success',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('quantity', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('stock_status')
                    ->label('Stock Status')
                    ->options([
                        'out' => 'Out of Stock',
                        'low' => 'Low (< 10)',
                        'ok'  => 'In Stock',
                    ])
                    ->query(fn ($query, $data) => match($data['value'] ?? null) {
                        'out' => $query->where('quantity', 0),
                        'low' => $query->where('quantity', '>', 0)->where('quantity', '<', 10),
                        'ok'  => $query->where('quantity', '>=', 10),
                        default => $query,
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('upload_excel')
                    ->label('Upload Inventory')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('Excel File (.xlsx)')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $pharmacy = self::getCurrentPharmacy();
                        $repo     = app(InventoryRepository::class);
                        $import   = new InventoryImport($pharmacy->id, $repo);

                        Excel::import($import, storage_path('app/public/' . $data['file']));

                        Notification::make()
                            ->title($import->getAddedCount() . ' items updated, ' . $import->getSkippedCount() . ' skipped')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
        ];
    }
}
