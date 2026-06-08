<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RatePharmacyRequest;
use App\Services\ReviewService;
use App\Models\Pharmacy;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected ReviewService $reviewService;
    
    public function __construct(ReviewService $reviewService)
    {
        $this->middleware('auth:sanctum');
        $this->reviewService = $reviewService;
    }
    
    // Add or update rating
    public function rate($id, RatePharmacyRequest $request)
    {
        $user = auth()->user();
        
        $pharmacy = Pharmacy::find($id);
        
        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }
        
        // Only customers can rate
        if ($user->role !== 'customer') {
            return response()->json(['message' => 'Only customers can rate pharmacies'], 403);
        }
        
        $result = $this->reviewService->addRating(
            $user->id,
            $id,
            $request->rating,
            $request->comment
        );
        
        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 400);
        }
        
        return response()->json([
            'message' => $result['message'],
            'review' => $result['review']
        ], 201);
    }
    
    // Get reviews for a pharmacy
    public function pharmacyReviews($id, Request $request)
    {
        $pharmacy = Pharmacy::find($id);
        
        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }
        
        $perPage = $request->get('per_page', 15);
        $reviews = $this->reviewService->getPharmacyReviews($id, $perPage);
        
        // Calculate average rating
        $avgRating = $pharmacy->reviews()->avg('rating') ?? 0;
        
        return response()->json([
            'pharmacy' => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
                'average_rating' => round($avgRating, 1),
                'total_reviews' => $pharmacy->reviews()->count(),
            ],
            'reviews' => $reviews,
        ]);
    }
}