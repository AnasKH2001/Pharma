<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchMedicineRequest;
use App\Models\Medicine;
use App\Services\SearchService;
use App\Services\HistoryService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected SearchService $searchService;
    protected HistoryService $historyService;

    public function __construct(SearchService $searchService, HistoryService $historyService)
    {
        $this->searchService = $searchService;
        $this->historyService = $historyService;
    }

    // API 1: Search medicines (autocomplete)
    public function searchMedicines(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);
        
        $medicines = Medicine::where('brand_name', 'LIKE', "%{$request->query('query')}%")
            ->orWhere('generic_name', 'LIKE', "%{$request->query('query')}%")
            ->limit(20)
            ->get(['id', 'brand_name', 'generic_name', 'dosage', 'form']);
        
        return response()->json([
            'medicines' => $medicines
        ]);
    }

    // API 2: Find pharmacies by medicine IDs
    public function findPharmacies(SearchMedicineRequest $request)
    {
        $userId = auth()->check() ? auth()->id() : null;
        
        // Record search history
        $this->historyService->recordSearch($userId, $request->medicine_ids);
        
        $result = $this->searchService->findPharmaciesByMedicines(
            $request->medicine_ids,
            $request->latitude,
            $request->longitude,
            $request->radius ?? 5,
            $userId
        );
        
        return response()->json($result);
    }
}