<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Models\Pharmacy;
use App\Models\OrderOffer;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->middleware('auth:sanctum');
        $this->orderService = $orderService;
    }

    // ============== PHARMACY ENDPOINTS ==============

    // Create new order
    public function createOrder(Request $request)
    {
        $user = auth()->user();
        
        // Only pharmacies can create orders
        if ($user->role !== 'pharmacy') {
            return response()->json(['message' => 'Only pharmacies can create orders'], 403);
        }
        
        $pharmacy = Pharmacy::where('email', $user->email)->first();
        
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        
        $order = $this->orderService->createOrder($pharmacy->id, $request->items);
        
        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order
        ], 201);
    }

    // Get pharmacy orders
    public function myOrders(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'pharmacy') {
            return response()->json(['message' => 'Only pharmacies can view orders'], 403);
        }
        
        $pharmacy = Pharmacy::where('email', $user->email)->first();
        
        $perPage = $request->get('per_page', 15);
        $orders = $this->orderService->getPharmacyOrders($pharmacy->id, $perPage);
        
        return response()->json($orders);
    }

    // Get order details
    public function orderDetails($id)
    {
        $user = auth()->user();
        $order = $this->orderService->getOrderDetails($id);
        
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        
        // Check permission
        if ($user->role === 'pharmacy') {
            $pharmacy = Pharmacy::where('email', $user->email)->first();
            if ($order->pharmacy_id !== $pharmacy->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } elseif ($user->role !== 'supplier' && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json(['order' => $order]);
    }

    // Get offers for an order (pharmacy view) with pagination
    public function orderOffers(Request $request, $orderId)
    {
        $user = auth()->user();
        
        if ($user->role !== 'pharmacy') {
            return response()->json(['message' => 'Only pharmacies can view offers'], 403);
        }
        
        $pharmacy = Pharmacy::where('email', $user->email)->first();
        $order = $this->orderService->getOrderDetails($orderId);
        
        if (!$order || $order->pharmacy_id !== $pharmacy->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        
        $offers = OrderOffer::with(['supplier', 'itemOffers.orderItem.medicine'])
            ->where('order_id', $orderId);
        
        if ($status) {
            $offers->where('status', $status);
        }
        
        $offers = $offers->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json([
            'order_id' => $orderId,
            'offers' => $offers,
            'total' => $offers->total()
        ]);
    }

    // Accept an offer (pharmacy)
    public function acceptOffer($offerId)
    {
        $user = auth()->user();
        
        if ($user->role !== 'pharmacy') {
            return response()->json(['message' => 'Only pharmacies can accept offers'], 403);
        }
        
        $offer = OrderOffer::with('order')->find($offerId);
        
        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }
        
        $pharmacy = Pharmacy::where('email', $user->email)->first();
        
        if ($offer->order->pharmacy_id !== $pharmacy->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        if ($offer->status !== 'pending') {
            return response()->json(['message' => 'This offer is no longer available'], 400);
        }
        
        $acceptedOffer = $this->orderService->acceptOffer($offerId);
        
        return response()->json([
            'message' => 'Offer accepted successfully',
            'offer' => $acceptedOffer
        ]);
    }

    // Cancel order (pharmacy)
    public function cancelOrder($id)
    {
        $user = auth()->user();
        
        if ($user->role !== 'pharmacy') {
            return response()->json(['message' => 'Only pharmacies can cancel orders'], 403);
        }
        
        $pharmacy = Pharmacy::where('email', $user->email)->first();
        
        $result = $this->orderService->cancelOrder($id, $pharmacy->id);
        
        if (!$result) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        
        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 400);
        }
        
        return response()->json([
            'message' => 'Order cancelled successfully',
            'order' => $result
        ]);
    }
}