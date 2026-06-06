<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadInventoryRequest;
use App\Models\Pharmacy;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->middleware('auth:sanctum');
        $this->inventoryService = $inventoryService;
    }

    public function upload(UploadInventoryRequest $request)
    {
        $pharmacy = Pharmacy::where('email', $request->user()->email)->first();
        $pharmacyId = $pharmacy->id;

        $result = $this->inventoryService->uploadInventory(
            $request->file('file'),
            $pharmacyId
        );

        return response()->json([
            'message' => "{$result['added']} items added/updated, {$result['skipped']} skipped",
            'errors' => $result['errors']
        ]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $pharmacy = Pharmacy::where('email', $user->email)->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $perPage = $request->get('per_page', 15);
        $inventory = $this->inventoryService->getPharmacyInventory($pharmacy->id, $perPage);

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
                    'manufacturer' => $item->medicine->manufacturer,
                ],
                'quantity' => $item->quantity,
                'price' => $item->price,
                'last_updated' => $item->updated_at,
            ];
        });

        return response()->json([
            'pharmacy' => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
            ],
            'inventory' => $inventory,
        ]);
    }
}
