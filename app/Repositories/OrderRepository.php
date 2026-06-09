<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;

class OrderRepository
{
    public function createOrder($pharmacyId, $orderDate, $status = 'pending')
    {
        return Order::create([
            'pharmacy_id' => $pharmacyId,
            'order_date' => $orderDate,
            'status' => $status,
        ]);
    }

    public function addOrderItem($orderId, $medicineId, $quantity)
    {
        return OrderItem::create([
            'order_id' => $orderId,
            'medicine_id' => $medicineId,
            'quantity' => $quantity,
        ]);
    }

    public function getPharmacyOrders($pharmacyId, $perPage = 15)
    {
        return Order::with(['orderItems.medicine'])
            ->where('pharmacy_id', $pharmacyId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getOrderById($orderId)
    {
        return Order::with(['orderItems.medicine', 'orderOffers.supplier'])
            ->find($orderId);
    }

    public function getPendingOrders($perPage = 15)
    {
        return Order::with(['pharmacy', 'orderItems.medicine'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }
}