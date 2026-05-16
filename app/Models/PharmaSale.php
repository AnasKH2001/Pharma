<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmaSale extends Model
{
    use HasFactory;

    protected $table = 'pharma_sales';

    protected $fillable = [
        'pharmacy_id',
        'medicine_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'total_price' => 'integer',
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

    // Boot method to auto-calculate total_price
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            $sale->total_price = $sale->quantity * $sale->unit_price;
        });
    }
}