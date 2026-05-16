<?php

namespace App\Repositories;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AdminRepository
{
    public function getPendingPharmacies()
    {
        return Pharmacy::where('is_active', false)
            ->orderBy('created_at', 'asc')
            ->get();
    }
    
    public function getApprovedPharmacies()
    {
        return Pharmacy::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }
    
    public function approvePharmacy(Pharmacy $pharmacy)
    {
        $pharmacy->update(['is_active' => true]);
        return $pharmacy;
    }
    
    public function rejectPharmacy(Pharmacy $pharmacy)
    {
        $user = User::where('email', $pharmacy->email)->first();
        if ($user) {
            $user->delete();
        }
    }
    
    public function getUserByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }
    
    public function getPharmacyCredentials(Pharmacy $pharmacy)
    {
        if (!$pharmacy->credentials) {
            return [];
        }
        
        $files = Storage::disk('public')->files($pharmacy->credentials);
        $credentials = [];
        
        foreach ($files as $file) {
            $credentials[] = [
                'name' => basename($file),
                'url' => asset('storage/' . $file)
            ];
        }
        
        return $credentials;
    }
}