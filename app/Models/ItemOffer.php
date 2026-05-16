<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_offer_id',
        'order_item_id',
        'price',
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    // Relationships
    public function orderOffer()
    {
        return $this->belongsTo(OrderOffer::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}