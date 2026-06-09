<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Models\OrderOffer;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->middleware('auth:sanctum');
        $this->orderService = $orderService;
    }

    // Get pending orders with distance (available for suppliers to bid on)
    public function pendingOrders(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'supplier') {
            return response()->json(['message' => 'Only suppliers can view pending orders'], 403);
        }
        
        $request->validate([
            'latitude' => 'required|numeric|between:32.0,37.5',
            'longitude' => 'required|numeric|between:35.5,42.5',
        ]);
        
        $perPage = $request->get('per_page', 15);
        $orders = $this->orderService->getPendingOrdersWithDistance(
            $request->latitude,
            $request->longitude,
            $perPage
        );
        
        return response()->json($orders);
    }

    // Make an offer on an order
    public function makeOffer(Request $request, $orderId)
    {
        $user = auth()->user();
        
        if ($user->role !== 'supplier') {
            return response()->json(['message' => 'Only suppliers can make offers'], 403);
        }
        
        $request->validate([
            'description' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.price' => 'required|integer|min:1',
        ]);
        
        $offer = $this->orderService->makeOffer(
            $orderId,
            $user->id,
            $request->description,
            $request->items
        );
        
        return response()->json([
            'message' => 'Offer submitted successfully',
            'offer' => $offer
        ], 201);
    }

    // Get supplier's offers with status filter
    public function myOffers(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'supplier') {
            return response()->json(['message' => 'Only suppliers can view their offers'], 403);
        }
        
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        
        $offers = OrderOffer::with(['order.pharmacy', 'itemOffers.orderItem.medicine'])
            ->where('supplier_id', $user->id);
        
        if ($status) {
            $offers->where('status', $status);
        }
        
        $offers = $offers->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json($offers);
    }

    // Cancel offer (supplier)
    public function cancelOffer($offerId)
    {
        $user = auth()->user();
        
        if ($user->role !== 'supplier') {
            return response()->json(['message' => 'Only suppliers can cancel offers'], 403);
        }
        
        $result = $this->orderService->cancelOffer($offerId, $user->id);
        
        if (!$result) {
            return response()->json(['message' => 'Offer not found'], 404);
        }
        
        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 400);
        }
        
        return response()->json([
            'message' => 'Offer cancelled successfully',
            'offer' => $result
        ]);
    }
}