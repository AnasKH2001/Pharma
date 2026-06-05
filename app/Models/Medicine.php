<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_name',
        'manufacturer',
        'generic_name',
        'dosage',
        'form',
    ];

    // Relationships
    public function pharmacies()
    {
        return $this->belongsToMany(Pharmacy::class, 'inventories')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function sales()
    {
        return $this->hasMany(PharmaSale::class);
    }

    public function lowStockNotifications()
    {
        return $this->hasMany(LowStockNotification::class);
    }

    // Accessor for full name
    public function getFullNameAttribute()
    {
        return "{$this->brand_name} ({$this->generic_name}) - {$this->dosage}";
    }

    public function pharmaciesInStock()
    {
        return $this->belongsToMany(Pharmacy::class, 'inventories')
            ->wherePivot('quantity', '>', 0)
            ->withPivot('quantity', 'price');
    }
}