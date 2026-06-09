<?php

namespace App\Services;

use App\Models\ItemOffer;
use App\Models\OfferAcceptedNotification;
use App\Models\Order;
use App\Models\OrderOffer;
use App\Models\OrderOfferNotification;
use App\Repositories\OrderRepository;

class OrderService
{
    protected OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    // Create new order
    public function createOrder($pharmacyId, $items)
    {
        $order = $this->orderRepository->createOrder($pharmacyId, now()->toDateString());

        foreach ($items as $item) {
            $this->orderRepository->addOrderItem(
                $order->id,
                $item['medicine_id'],
                $item['quantity']
            );
        }

        return $order->load('orderItems.medicine');
    }

    // Get pharmacy orders
    public function getPharmacyOrders($pharmacyId, $perPage = 15)
    {
        return $this->orderRepository->getPharmacyOrders($pharmacyId, $perPage);
    }

    // Get order details
    public function getOrderDetails($orderId)
    {
        return $this->orderRepository->getOrderById($orderId);
    }

    // Get pending orders without distance (original)
    public function getPendingOrders($perPage = 15)
    {
        return $this->orderRepository->getPendingOrders($perPage);
    }

    // Get pending orders with distance calculation for suppliers
    public function getPendingOrdersWithDistance($supplierLat, $supplierLng, $perPage = 15)
    {
        $orders = $this->orderRepository->getPendingOrders($perPage);
        
        // Calculate distance for each order's pharmacy
        foreach ($orders->getCollection() as $order) {
            if ($order->pharmacy) {
                $order->distance = $this->calculateDistance(
                    $supplierLat,
                    $supplierLng,
                    (float) $order->pharmacy->latitude,
                    (float) $order->pharmacy->longitude
                );
            } else {
                $order->distance = null;
            }
        }
        
        // Sort by distance (nearest first)
        $orders->getCollection()->sortBy('distance');
        
        return $orders;
    }

    // Supplier makes offer on an order
    public function makeOffer($orderId, $supplierId, $description, $items)
    {
        $orderOffer = OrderOffer::create([
            'order_id' => $orderId,
            'supplier_id' => $supplierId,
            'description' => $description,
            'status' => 'pending',
        ]);

        foreach ($items as $item) {
            ItemOffer::create([
                'order_offer_id' => $orderOffer->id,
                'order_item_id' => $item['order_item_id'],
                'price' => $item['price'],
            ]);
        }

        // Notify pharmacy that an offer was received
        OrderOfferNotification::create([
            'pharmacy_id' => $orderOffer->order->pharmacy_id,
            'order_offer_id' => $orderOffer->id,
        ]);

        return $orderOffer->load('itemOffers');
    }

    // Get offers for an order (pharmacy view)
    public function getOrderOffers($orderId)
    {
        return OrderOffer::with(['supplier', 'itemOffers.orderItem.medicine'])
            ->where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Get supplier's offers
    public function getSupplierOffers($supplierId, $perPage = 15)
    {
        return OrderOffer::with(['order.pharmacy', 'itemOffers.orderItem.medicine'])
            ->where('supplier_id', $supplierId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    // Accept an offer
    public function acceptOffer($offerId)
    {
        $offer = OrderOffer::with('order')->findOrFail($offerId);
        
        // Update offer status
        $offer->update(['status' => 'accepted']);
        
        // Update order status
        $offer->order->update(['status' => 'assigned']);
        
        // Reject all other offers for this order
        OrderOffer::where('order_id', $offer->order_id)
            ->where('id', '!=', $offerId)
            ->update(['status' => 'rejected']);
        
        // Notify supplier that their offer was accepted
        OfferAcceptedNotification::create([
            'supplier_id' => $offer->supplier_id,
            'order_offer_id' => $offer->id,
        ]);
        
        return $offer;
    }

    // Cancel order (pharmacy)
    public function cancelOrder($orderId, $pharmacyId)
    {
        $order = Order::where('pharmacy_id', $pharmacyId)
            ->where('id', $orderId)
            ->first();
        
        if (!$order) {
            return null;
        }
        
        if ($order->status !== 'pending') {
            return ['error' => 'Only pending orders can be cancelled'];
        }
        
        $order->update(['status' => 'cancelled']);
        
        // Reject all pending offers for this order
        OrderOffer::where('order_id', $orderId)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
        
        return $order;
    }

    // Cancel offer (supplier)
    public function cancelOffer($offerId, $supplierId)
    {
        $offer = OrderOffer::where('supplier_id', $supplierId)
            ->where('id', $offerId)
            ->first();
        
        if (!$offer) {
            return null;
        }
        
        if ($offer->status !== 'pending') {
            return ['error' => 'Only pending offers can be cancelled'];
        }
        
        $offer->update(['status' => 'cancelled']);
        
        return $offer;
    }

    // Calculate distance between two coordinates (Haversine formula)
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // kilometers
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return round($earthRadius * $c, 2);
    }
}