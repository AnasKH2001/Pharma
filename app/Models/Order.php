<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'supplier_id',
        'order_date',
        'status',
    ];

    protected $casts = [
        'order_date' => 'date',
    ];

    // Relationships
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderOffers()
    {
        return $this->hasMany(OrderOffer::class);
    }
}