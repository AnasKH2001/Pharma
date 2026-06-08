<?php

namespace App\Services;

use App\Repositories\ReviewRepository;

class ReviewService
{
    protected ReviewRepository $reviewRepository;
    
    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }
    
    public function addRating($userId, $pharmacyId, $rating, $comment = null)
    {
        // Check if user already rated this pharmacy
        $existing = $this->reviewRepository->getUserReview($userId, $pharmacyId);
        
        if ($existing) {
            return [
                'success' => false,
                'message' => 'You have already rated this pharmacy',
                'existing_rating' => $existing->rating,
            ];
        }
        
        $review = $this->reviewRepository->create([
            'user_id' => $userId,
            'pharmacy_id' => $pharmacyId,
            'rating' => $rating,
            'comment' => $comment,
        ]);
        
        return [
            'success' => true,
            'message' => 'Rating added successfully',
            'review' => $review,
        ];
    }
    
    public function getPharmacyReviews($pharmacyId, $perPage = 15)
    {
        return $this->reviewRepository->getPharmacyReviews($pharmacyId, $perPage);
    }
}