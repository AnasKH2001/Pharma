<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
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

    // Get all notifications for the pharmacy
    public function index(Request $request)
    {
        $user = auth()->user();

        $pharmacy = Pharmacy::where('email', $user->email)->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $perPage = $request->get('per_page', 15);
        $notifications = $this->notificationService->getNotifications($pharmacy->id, $perPage);

        // Format the paginated data
        $notifications->getCollection()->transform(function ($notification) {
            return [
                'id' => $notification->id,
                'medicine' => [
                    'id' => $notification->medicine->id,
                    'brand_name' => $notification->medicine->brand_name,
                    'generic_name' => $notification->medicine->generic_name,
                ],
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'notifications' => $notifications,
            'total' => $notifications->total()
        ]);
    }

    // Mark as read (actually deletes)
    public function markAsRead($id)
    {
        $this->notificationService->markAsRead($id);

        return response()->json([
            'message' => 'Notification removed'
        ]);
    }

    // Mark all as read (actually deletes all)
    public function markAllAsRead()
    {
        $user = auth()->user();

        $pharmacy = Pharmacy::where('email', $user->email)->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $this->notificationService->markAllAsRead($pharmacy->id);

        return response()->json([
            'message' => 'All notifications cleared'
        ]);
    }
}
