<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'supplier_id',
        'description',
        'status',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function itemOffers()
    {
        return $this->hasMany(ItemOffer::class);
    }

    public function offerAcceptedNotifications()
    {
        return $this->hasMany(OfferAcceptedNotification::class);
    }

    public function orderOfferNotifications()
    {
        return $this->hasMany(OrderOfferNotification::class);
    }

    // Helper methods
    public function accept()
    {
        $this->update(['status' => 'accepted']);
        
        // Notify supplier that their offer was accepted
        OfferAcceptedNotification::create([
            'supplier_id' => $this->supplier_id,
            'order_offer_id' => $this->id,
        ]);
    }

    public function reject()
    {
        $this->update(['status' => 'rejected']);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
}