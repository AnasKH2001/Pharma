<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferRejectedNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'order_offer_id',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function orderOffer()
    {
        return $this->belongsTo(OrderOffer::class);
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }
}