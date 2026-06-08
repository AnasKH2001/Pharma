<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'otp',
        'otp_expires_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'otp_expires_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isPharmacy(): bool
    {
        return $this->role === 'pharmacy';
    }

    public function isSupplier(): bool
    {
        return $this->role === 'supplier';
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function lowStockNotifications()
    {
        return $this->hasMany(LowStockNotification::class, 'pharmacy_id');
    }

    public function orderOfferNotifications()
    {
        return $this->hasMany(OrderOfferNotification::class, 'pharmacy_id');
    }

    public function offerAcceptedNotifications()
    {
        return $this->hasMany(OfferAcceptedNotification::class, 'supplier_id');
    }

    public function orderOffers()
    {
        return $this->hasMany(OrderOffer::class, 'supplier_id');
    }

    public function favoritePharmacies()
    {
        return $this->belongsToMany(Pharmacy::class, 'favorite_pharmacies')
            ->withTimestamps();
    }

    public function favoriteMedicines()
    {
        return $this->belongsToMany(Medicine::class, 'favorite_medicines')
            ->withTimestamps();
    }

    public function isPharmacyFavorite($pharmacyId)
    {
        return $this->favoritePharmacies()->where('pharmacy_id', $pharmacyId)->exists();
    }

    public function isMedicineFavorite($medicineId)
    {
        return $this->favoriteMedicines()->where('medicine_id', $medicineId)->exists();
    }
}
