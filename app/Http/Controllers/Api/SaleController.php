<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaleRequest;
use App\Services\SaleService;
use App\Models\Pharmacy;

class SaleController extends Controller
{
    protected SaleService $saleService;
    
    public function __construct(SaleService $saleService)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('pharmacy.approved');
        $this->saleService = $saleService;
    }
    
    public function store(SaleRequest $request)
    {
        $user = auth()->user();
        
        $pharmacy = Pharmacy::where('email', $user->email)->first();
        
        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }
        
        try {
            $result = $this->saleService->recordSale(
                $pharmacy->id,
                $request->medicine_id,
                $request->quantity
            );
            
            return response()->json([
                'message' => 'Sale recorded successfully',
                'sale' => $result['sale'],
                'remaining_stock' => $result['remaining_stock']
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    
    public function index()
    {
        $user = auth()->user();
        
        $pharmacy = Pharmacy::where('email', $user->email)->first();
        
        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }
        
        $sales = $this->saleService->getSalesHistory($pharmacy->id);
        
        return response()->json([
            'sales' => $sales,
            'total' => $sales->count()
        ]);
    }
}