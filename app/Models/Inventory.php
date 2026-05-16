<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
        'pharmacy_id',
        'medicine_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'integer',
    ];

    // Relationships
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    // Helper methods
    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }

    public function decrementStock(int $quantity): void
    {
        $this->decrement('quantity', $quantity);
        
        if ($this->quantity === 0) {
            LowStockNotification::create([
                'pharmacy_id' => $this->pharmacy_id,
                'medicine_id' => $this->medicine_id,
            ]);
        }
    }

    public function incrementStock(int $quantity): void
    {
        $this->increment('quantity', $quantity);
    }
}