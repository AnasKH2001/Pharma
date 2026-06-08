<?php

namespace App\Repositories;

use App\Models\Review;

class ReviewRepository
{
    public function create(array $data): Review
    {
        return Review::create($data);
    }
    
    public function getUserReview($userId, $pharmacyId)
    {
        return Review::where('user_id', $userId)
            ->where('pharmacy_id', $pharmacyId)
            ->first();
    }
    
    public function getPharmacyReviews($pharmacyId, $perPage = 15)
    {
        return Review::with('user')
            ->where('pharmacy_id', $pharmacyId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    public function updateRating($reviewId, $rating, $comment = null)
    {
        $review = Review::find($reviewId);
        
        if ($review) {
            $review->update([
                'rating' => $rating,
                'comment' => $comment,
            ]);
        }
        
        return $review;
    }
}