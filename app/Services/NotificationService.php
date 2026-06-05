<?php

namespace App\Services;

use App\Repositories\NotificationRepository;

class NotificationService
{
    protected NotificationRepository $notificationRepository;
    
    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }
    
    public function getNotifications($pharmacyId)
    {
        return $this->notificationRepository->getByPharmacy($pharmacyId);
    }
    
    public function markAsRead($notificationId)
    {
        return $this->notificationRepository->delete($notificationId);
    }
    
    public function markAllAsRead($pharmacyId)
    {
        return $this->notificationRepository->deleteAll($pharmacyId);
    }
}