<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchMedicineRequest;
use App\Models\Medicine;
use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
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
        $result = $this->searchService->findPharmaciesByMedicines(
            $request->medicine_ids,
            $request->latitude,
            $request->longitude,
            $request->radius ?? 5
        );
        
        return response()->json($result);
    }
}