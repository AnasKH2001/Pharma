<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'credentials',
        'phone',
        'email',
        'address',
        'latitude',
        'longitude',
        'opens_at',
        'closes_at',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function medicines()
    {
        return $this->belongsToMany(Medicine::class, 'inventories')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function sales()
    {
        return $this->hasMany(PharmaSale::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function lowStockNotifications()
    {
        return $this->hasMany(LowStockNotification::class);
    }

    public function orderOfferNotifications()
    {
        return $this->hasMany(OrderOfferNotification::class);
    }

    // Helper method to check if open
    public function isOpen(): bool
    {
        $now = now();
        $currentTime = $now->format('H:i:s');

        // Convert opens_at and closes_at to time strings
        $openTime = date('H:i:s', strtotime($this->opens_at));
        $closeTime = date('H:i:s', strtotime($this->closes_at));

        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }

    public function getFormattedOpeningHours(): string
    {
        $openTime = date('H:i', strtotime($this->opens_at));
        $closeTime = date('H:i', strtotime($this->closes_at));

        return "{$openTime} - {$closeTime}";
    }

    // Get average rating
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }
}
