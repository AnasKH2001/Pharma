<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfferAcceptedNotification;
use App\Models\OfferRejectedNotification;
use Illuminate\Http\Request;

class SupplierNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Get offer accepted notifications (supplier)
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'supplier') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = $request->get('per_page', 15);
        $onlyUnread = $request->get('only_unread', false);

        $query = OfferAcceptedNotification::with('orderOffer.order.pharmacy')
            ->where('supplier_id', $user->id);

        if ($onlyUnread) {
            $query->where('is_read', false);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $unreadCount = OfferAcceptedNotification::where('supplier_id', $user->id)
            ->where('is_read', false)
            ->count();

        // Format the paginated data
        $notifications->getCollection()->transform(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => 'offer_accepted',
                'offer_id' => $notification->order_offer_id,
                'offer_status' => $notification->orderOffer->status ?? null,
                'order_id' => $notification->orderOffer->order_id,
                'pharmacy_name' => $notification->orderOffer->order->pharmacy->name ?? null,
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

    public function rejectedNotifications()
    {
        $user = auth()->user();

        $notifications = OfferRejectedNotification::with('orderOffer')
            ->where('supplier_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    // Mark notification as read
    public function markAsRead($id)
    {
        $user = auth()->user();

        $notification = OfferAcceptedNotification::where('supplier_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    // Mark all notifications as read
    public function markAllAsRead()
    {
        $user = auth()->user();

        OfferAcceptedNotification::where('supplier_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    // Delete notification (optional, if you still want delete capability)
    public function destroy($id)
    {
        $user = auth()->user();

        $notification = OfferAcceptedNotification::where('supplier_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted']);
    }

    // Clear all notifications (delete all)
    public function clearAll()
    {
        $user = auth()->user();

        OfferAcceptedNotification::where('supplier_id', $user->id)->delete();

        return response()->json(['message' => 'All notifications cleared']);
    }
}
