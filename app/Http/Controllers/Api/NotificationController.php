<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\OrderOfferNotification;
use App\Models\LowStockNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('pharmacy.approved');
        $this->notificationService = $notificationService;
    }

    // Get low stock notifications for the pharmacy
    public function index(Request $request)
    {
        $user = auth()->user();

        $pharmacy = Pharmacy::where('email', $user->email)->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $perPage = $request->get('per_page', 15);
        $notifications = $this->notificationService->getNotifications($pharmacy->id, $perPage);

        $notifications->getCollection()->transform(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => 'low_stock',
                'medicine' => [
                    'id' => $notification->medicine->id,
                    'brand_name' => $notification->medicine->brand_name,
                    'generic_name' => $notification->medicine->generic_name,
                ],
                'is_read' => $notification->is_read,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });

        $unreadCount = $this->notificationService->getUnreadCount($pharmacy->id);

        return response()->json([
            'notifications' => $notifications,
            'total' => $notifications->total(),
            'unread_count' => $unreadCount
        ]);
    }

    // Get order offer notifications (pharmacy) with status filter
    public function orderOfferNotifications(Request $request)
    {
        $user = auth()->user();

        $pharmacy = Pharmacy::where('email', $user->email)->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $onlyUnread = filter_var($request->get('only_unread', false), FILTER_VALIDATE_BOOLEAN);

        $query = OrderOfferNotification::with('orderOffer.order', 'orderOffer.supplier')
            ->where('pharmacy_id', $pharmacy->id);

        if ($status) {
            $query->whereHas('orderOffer', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        if ($onlyUnread) {
            $query->where('is_read', false);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $unreadCount = OrderOfferNotification::where('pharmacy_id', $pharmacy->id)
            ->where('is_read', false)
            ->count();

        $notifications->getCollection()->transform(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => 'offer_received',
                'offer_id' => $notification->order_offer_id,
                'offer_status' => $notification->orderOffer->status,
                'order_id' => $notification->orderOffer->order_id,
                'supplier_name' => $notification->orderOffer->supplier->name ?? null,
                'is_read' => $notification->is_read,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'notifications' => $notifications,
            'total' => $notifications->total(),
            'unread_count' => $unreadCount
        ]);
    }

    // Mark order offer notification as read
    public function markOrderOfferRead($id)
    {
        $notification = OrderOfferNotification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    // Mark all order offer notifications as read
    public function markAllOrderOffersRead()
    {
        $user = auth()->user();

        $pharmacy = Pharmacy::where('email', $user->email)->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        OrderOfferNotification::where('pharmacy_id', $pharmacy->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All offer notifications marked as read']);
    }

    // Mark low stock notification as read
    public function markAsRead($id)
    {
        $this->notificationService->markAsRead($id);

        return response()->json(['message' => 'Notification marked as read']);
    }

    // Mark all low stock notifications as read
    public function markAllAsRead()
    {
        $user = auth()->user();

        $pharmacy = Pharmacy::where('email', $user->email)->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $this->notificationService->markAllAsRead($pharmacy->id);

        return response()->json(['message' => 'All low stock notifications marked as read']);
    }

    // Get low stock notifications only (separate endpoint)
    public function lowStockNotifications(Request $request)
    {
        $user = auth()->user();
        $pharmacy = Pharmacy::where('email', $user->email)->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $perPage = $request->get('per_page', 15);
        $isRead = $request->has('is_read') ? filter_var($request->get('is_read'), FILTER_VALIDATE_BOOLEAN) : null;

        $query = LowStockNotification::where('pharmacy_id', $pharmacy->id)
            ->with('medicine')
            ->orderBy('created_at', 'desc');

        if ($isRead !== null) {
            $query->where('is_read', $isRead);
        }

        $notifications = $query->paginate($perPage);

        $notifications->getCollection()->transform(function ($notification) {
            return [
                'id' => $notification->id,
                'medicine' => [
                    'id' => $notification->medicine->id,
                    'brand_name' => $notification->medicine->brand_name,
                    'generic_name' => $notification->medicine->generic_name,
                ],
                'is_read' => $notification->is_read,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });

        $unreadCount = LowStockNotification::where('pharmacy_id', $pharmacy->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'total' => $notifications->total(),
            'unread_count' => $unreadCount
        ]);
    }
}
