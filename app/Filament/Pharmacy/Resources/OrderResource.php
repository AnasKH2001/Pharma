<?php

namespace App\Filament\Pharmacy\Resources;

use App\Filament\Pharmacy\Concerns\HasCurrentPharmacy;
use App\Filament\Pharmacy\Resources\OrderResource\Pages;
use App\Models\Medicine;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    use HasCurrentPharmacy;

    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('pharmacy_id', self::getCurrentPharmacy()->id)
            ->with('orderItems.medicine');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Repeater::make('items')
                ->label('Order Items')
                ->schema([
                    Forms\Components\Select::make('medicine_id')
                        ->label('Medicine')
                        ->options(
                            Medicine::all()->mapWithKeys(fn ($m) => [
                                $m->id => "{$m->brand_name} ({$m->generic_name}) - {$m->dosage}",
                            ])
                        )
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('quantity')
                        ->numeric()
                        ->required()
                        ->minValue(1),
                ])
                ->columns(2)
                ->minItems(1)
                ->required()
                ->addActionLabel('Add Medicine'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order #')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('orderItems_count')
                    ->label('Items')
                    ->state(fn (Order $record) => $record->orderItems->count()),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'assigned',
                        'danger'  => 'cancelled',
                        'gray'    => 'completed',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'assigned'  => 'Assigned',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $result = app(OrderService::class)->cancelOrder($record->id, self::getCurrentPharmacy()->id);

                        if (isset($result['error'])) {
                            Notification::make()->title($result['error'])->danger()->send();
                            return;
                        }

                        Notification::make()->title('Order cancelled')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view'   => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
