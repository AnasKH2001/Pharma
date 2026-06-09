<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use App\Models\LowStockNotification;

class NotificationService
{
    protected NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function getNotifications($pharmacyId, $perPage = 15)
    {
        return $this->notificationRepository->getByPharmacy($pharmacyId, $perPage);
    }

    public function getUnreadCount($pharmacyId)
    {
        return LowStockNotification::where('pharmacy_id', $pharmacyId)
            ->where('is_read', false)
            ->count();
    }

    public function markAsRead($notificationId)
    {
        $notification = LowStockNotification::find($notificationId);
        
        if ($notification) {
            $notification->update(['is_read' => true]);
        }
        
        return $notification;
    }

    public function markAllAsRead($pharmacyId)
    {
        return LowStockNotification::where('pharmacy_id', $pharmacyId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}