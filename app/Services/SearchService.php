<?php

namespace App\Services;

use App\Models\Pharmacy;

class SearchService
{
    public function findPharmaciesByMedicines(array $medicineIds, float $userLat, float $userLng, float $radius = 5, $userId = null)
    {
        // Find pharmacies that have ALL selected medicines in stock
        $pharmacies = Pharmacy::whereHas('inventories', function ($query) use ($medicineIds) {
            $query->whereIn('medicine_id', $medicineIds)
                ->where('quantity', '>', 0);
        }, '=', count($medicineIds))
            ->with(['inventories' => function ($query) use ($medicineIds) {
                $query->whereIn('medicine_id', $medicineIds)
                    ->where('quantity', '>', 0)
                    ->with('medicine');
            }])
            ->get();

        // Calculate distance
        foreach ($pharmacies as $pharmacy) {
            $pharmacy->distance = $this->calculateDistance(
                $userLat,
                $userLng,
                (float) $pharmacy->latitude,
                (float) $pharmacy->longitude
            );
        }

        // Filter by radius
        $pharmacies = $pharmacies->filter(function ($pharmacy) use ($radius) {
            return $pharmacy->distance <= $radius;
        });

        // Sort by distance (nearest first)
        $pharmacies = $pharmacies->sortBy('distance')->values();

        return [
            'pharmacies' => $pharmacies->map(function ($pharmacy) {
                return [
                    'id' => $pharmacy->id,
                    'name' => $pharmacy->name,
                    'address' => $pharmacy->address,
                    'phone' => $pharmacy->phone,
                    'distance' => $pharmacy->distance,
                    'is_open' => $pharmacy->isOpen(),
                    'opening_hours' => $pharmacy->getFormattedOpeningHours(),
                    'rating' => round($pharmacy->reviews()->avg('rating') ?? 0, 1),
                    'rating_count' => $pharmacy->reviews()->count(),
                    'inventories' => $pharmacy->inventories->map(function ($inventory) {
                        return [
                            'quantity' => $inventory->quantity,
                            'price' => $inventory->price,
                            'medicine' => [
                                'id' => $inventory->medicine->id,
                                'brand_name' => $inventory->medicine->brand_name,
                                'generic_name' => $inventory->medicine->generic_name,
                                'dosage' => $inventory->medicine->dosage,
                                'form' => $inventory->medicine->form,
                            ]
                        ];
                    }),
                ];
            }),
            'total_found' => $pharmacies->count()
        ];
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // kilometers

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}