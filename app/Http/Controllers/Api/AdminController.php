<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadMedicinesRequest;
use App\Imports\MedicinesImport;
use App\Models\Inventory;
use App\Models\Pharmacy;
use App\Services\AdminService;
use App\Services\InventoryService;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    protected AdminService $adminService;
    protected InventoryService $inventoryService;

    public function __construct(AdminService $adminService, InventoryService $inventoryService)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('admin');
        $this->adminService = $adminService;
        $this->inventoryService = $inventoryService;
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

    public function pharmacyInventory($id)
    {
        $pharmacy = Pharmacy::find($id);
        
        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }
        
        $inventory = $this->inventoryService->getPharmacyInventory($id);
        
        return response()->json([
            'pharmacy' => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
                'is_active' => $pharmacy->is_active,
            ],
            'inventory' => $inventory->map(function ($item) {
                return [
                    'id' => $item->id,
                    'medicine' => [
                        'id' => $item->medicine->id,
                        'brand_name' => $item->medicine->brand_name,
                        'generic_name' => $item->medicine->generic_name,
                        'dosage' => $item->medicine->dosage,
                        'form' => $item->medicine->form,
                    ],
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            }),
        ]);
    }
}