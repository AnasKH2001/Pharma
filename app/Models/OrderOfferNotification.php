<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderOfferNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'order_offer_id',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // Relationships
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function orderOffer()
    {
        return $this->belongsTo(OrderOffer::class);
    }

    // Mark as read
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }
}