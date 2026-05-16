<?php

namespace App\Services;

use App\Models\Pharmacy;
use App\Repositories\AdminRepository;

class AdminService
{
    protected AdminRepository $adminRepository;
    
    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }
    
    public function getPendingPharmacies()
    {
        $pharmacies = $this->adminRepository->getPendingPharmacies();
        
        // Attach user to each pharmacy
        foreach ($pharmacies as $pharmacy) {
            $pharmacy->user = $this->adminRepository->getUserByEmail($pharmacy->email);
        }
        
        return $pharmacies;
    }
    
    public function getApprovedPharmacies()
    {
        $pharmacies = $this->adminRepository->getApprovedPharmacies();
        
        foreach ($pharmacies as $pharmacy) {
            $pharmacy->user = $this->adminRepository->getUserByEmail($pharmacy->email);
        }
        
        return $pharmacies;
    }
    
    public function approvePharmacy(Pharmacy $pharmacy)
    {
        return $this->adminRepository->approvePharmacy($pharmacy);
    }
    
    public function rejectPharmacy(Pharmacy $pharmacy)
    {
        $this->adminRepository->rejectPharmacy($pharmacy);
    }
    
    public function getPharmacyDetails(Pharmacy $pharmacy)
    {
        $credentials = $this->adminRepository->getPharmacyCredentials($pharmacy);
        
        return [
            'pharmacy' => $pharmacy,
            'credentials' => $credentials
        ];
    }
}