<?php

namespace App\Services;

use App\Models\SearchHistory;

class HistoryService
{
    public function recordSearch($userId, array $medicineIds)
    {
        if (!$userId) {
            return;
        }
        
        foreach ($medicineIds as $medicineId) {
            // Delete old record for this medicine (keep only latest)
            SearchHistory::where('user_id', $userId)
                ->where('medicine_id', $medicineId)
                ->delete();
            
            // Create new record
            SearchHistory::create([
                'user_id' => $userId,
                'medicine_id' => $medicineId,
            ]);
        }
        
        // Keep only last 50 searches per user
        $count = SearchHistory::where('user_id', $userId)->count();
        if ($count > 50) {
            $oldest = SearchHistory::where('user_id', $userId)
                ->orderBy('created_at', 'asc')
                ->first();
            if ($oldest) {
                $oldest->delete();
            }
        }
    }
}