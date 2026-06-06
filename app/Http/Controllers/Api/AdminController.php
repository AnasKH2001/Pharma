<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadMedicinesRequest;
use App\Imports\MedicinesImport;
use App\Models\Pharmacy;
use App\Services\AdminService;
use App\Services\InventoryService;
use App\Services\StatService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    protected AdminService $adminService;
    protected InventoryService $inventoryService;
    protected StatService $statService;

    public function __construct(AdminService $adminService, InventoryService $inventoryService, StatService $statService)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('admin');
        $this->adminService = $adminService;
        $this->inventoryService = $inventoryService;
        $this->statService = $statService;
    }

    public function pendingPharmacies(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $pharmacies = $this->adminService->getPendingPharmacies($perPage);

        return response()->json($pharmacies);
    }

    public function approvedPharmacies(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $pharmacies = $this->adminService->getApprovedPharmacies($perPage);

        return response()->json($pharmacies);
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

    public function pharmacyInventory(Request $request, $id)
    {
        $pharmacy = Pharmacy::find($id);

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $perPage = $request->get('per_page', 15);
        $inventory = $this->inventoryService->getPharmacyInventory($id, $perPage);

        // Format the paginated data
        $inventory->getCollection()->transform(function ($item) {
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
        });

        return response()->json([
            'pharmacy' => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
                'is_active' => $pharmacy->is_active,
            ],
            'inventory' => $inventory,
        ]);
    }

    public function dashboardStats()
    {
        $stats = $this->statService->getAdminStats();

        return response()->json($stats);
    }

    public function topPharmacies(Request $request)
    {
        $limit = $request->get('limit', 10);
        $topPharmacies = $this->statService->getAdminTopPharmacies($limit);

        return response()->json([
            'top_pharmacies' => $topPharmacies,
            'total' => $topPharmacies->count()
        ]);
    }

    public function salesTrend(Request $request)
    {
        $days = $request->get('days', 30);
        $trend = $this->statService->getAdminSalesTrend($days);

        return response()->json([
            'trend' => $trend,
            'days' => $days
        ]);
    }
}
