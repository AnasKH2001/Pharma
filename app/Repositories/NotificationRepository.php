<?php

namespace App\Repositories;

use App\Models\LowStockNotification;

class NotificationRepository
{
    public function getByPharmacy($pharmacyId)
    {
        return LowStockNotification::with('medicine')
            ->where('pharmacy_id', $pharmacyId)
            ->orderBy('created_at', 'desc')
            ->get();
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
            ->update(['is_read' => true]);
    }
    
    public function delete($notificationId)
    {
        $notification = LowStockNotification::find($notificationId);
        
        if ($notification) {
            $notification->delete();
        }
        
        return true;
    }
    
    public function deleteAll($pharmacyId)
    {
        return LowStockNotification::where('pharmacy_id', $pharmacyId)->delete();
    }
}