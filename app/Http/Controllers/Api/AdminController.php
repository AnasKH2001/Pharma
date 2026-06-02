<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadMedicinesRequest;
use App\Imports\MedicinesImport;
use App\Models\Pharmacy;
use App\Services\AdminService;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    protected AdminService $adminService;
    
    public function __construct(AdminService $adminService)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('admin');
        $this->adminService = $adminService;
    }
    
    public function pendingPharmacies()
    {
        $pharmacies = $this->adminService->getPendingPharmacies();
        
        return response()->json(['pharmacies' => $pharmacies]);
    }
    
    public function approvedPharmacies()
    {
        $pharmacies = $this->adminService->getApprovedPharmacies();
        
        return response()->json(['pharmacies' => $pharmacies]);
    }
    
    public function approve(Pharmacy $pharmacy)
    {
        $pharmacy = $this->adminService->approvePharmacy($pharmacy);
        
        return response()->json([
            'message' => 'Pharmacy approved successfully',
            'pharmacy' => $pharmacy
        ]);
    }
    
    public function reject(Pharmacy $pharmacy)
    {
        $this->adminService->rejectPharmacy($pharmacy);
        
        return response()->json([
            'message' => 'Pharmacy rejected and deleted'
        ]);
    }
    
    public function show(Pharmacy $pharmacy)
    {
        $result = $this->adminService->getPharmacyDetails($pharmacy);
        
        return response()->json($result);
    }

    public function uploadMedicines(UploadMedicinesRequest $request)
    {
        $import = new MedicinesImport();
        Excel::import($import, $request->file('file'));
        
        $added = $import->getAddedCount();
        $skipped = $import->getSkippedCount();
        
        return response()->json([
            'message' => "{$added} medicines added, {$skipped} duplicates skipped"
        ]);
    }
}