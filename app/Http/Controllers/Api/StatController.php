<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatService;
use App\Models\Pharmacy;

class StatController extends Controller
{
    protected StatService $statService;
    
    public function __construct(StatService $statService)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('pharmacy.approved');
        $this->statService = $statService;
    }
    
    public function dashboard()
    {
        $user = auth()->user();
        
        $pharmacy = Pharmacy::where('email', $user->email)->first();
        
        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }
        
        $stats = $this->statService->getDashboardStats($pharmacy->id);
        
        return response()->json($stats);
    }
}