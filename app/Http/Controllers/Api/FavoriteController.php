<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\Medicine;
use App\Models\FavoritePharmacy;
use App\Models\FavoriteMedicine;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // ============== PHARMACY FAVORITES ==============

    // Add pharmacy to favorites
    public function addPharmacy($id)
    {
        $user = auth()->user();
        
        $pharmacy = Pharmacy::find($id);
        
        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }
        
        $exists = FavoritePharmacy::where('user_id', $user->id)
            ->where('pharmacy_id', $id)
            ->exists();
        
        if ($exists) {
            return response()->json(['message' => 'Pharmacy already in favorites'], 400);
        }
        
        FavoritePharmacy::create([
            'user_id' => $user->id,
            'pharmacy_id' => $id
        ]);
        
        return response()->json([
            'message' => 'Pharmacy added to favorites',
            'is_favorite' => true
        ]);
    }
    
    // Remove pharmacy from favorites
    public function removePharmacy($id)
    {
        $user = auth()->user();
        
        FavoritePharmacy::where('user_id', $user->id)
            ->where('pharmacy_id', $id)
            ->delete();
        
        return response()->json([
            'message' => 'Pharmacy removed from favorites',
            'is_favorite' => false
        ]);
    }
    
    // Get all favorite pharmacies
    public function getFavoritePharmacies()
    {
        $user = auth()->user();
        
        $pharmacies = Pharmacy::join('favorite_pharmacies', 'pharmacies.id', '=', 'favorite_pharmacies.pharmacy_id')
            ->where('favorite_pharmacies.user_id', $user->id)
            ->get(['pharmacies.*'])
            ->map(function ($pharmacy) {
                return [
                    'id' => $pharmacy->id,
                    'name' => $pharmacy->name,
                    'address' => $pharmacy->address,
                    'phone' => $pharmacy->phone,
                    'rating' => round($pharmacy->reviews()->avg('rating') ?? 0, 1),
                    'rating_count' => $pharmacy->reviews()->count(),
                    'is_open' => $pharmacy->isOpen(),
                    'opening_hours' => $pharmacy->getFormattedOpeningHours(),
                ];
            });
        
        return response()->json([
            'favorites' => $pharmacies,
            'total' => $pharmacies->count()
        ]);
    }

    // ============== MEDICINE FAVORITES ==============

    // Add medicine to favorites
    public function addMedicine($id)
    {
        $user = auth()->user();
        
        $medicine = Medicine::find($id);
        
        if (!$medicine) {
            return response()->json(['message' => 'Medicine not found'], 404);
        }
        
        $exists = FavoriteMedicine::where('user_id', $user->id)
            ->where('medicine_id', $id)
            ->exists();
        
        if ($exists) {
            return response()->json(['message' => 'Medicine already in favorites'], 400);
        }
        
        FavoriteMedicine::create([
            'user_id' => $user->id,
            'medicine_id' => $id
        ]);
        
        return response()->json([
            'message' => 'Medicine added to favorites',
            'is_favorite' => true
        ]);
    }
    
    // Remove medicine from favorites
    public function removeMedicine($id)
    {
        $user = auth()->user();
        
        FavoriteMedicine::where('user_id', $user->id)
            ->where('medicine_id', $id)
            ->delete();
        
        return response()->json([
            'message' => 'Medicine removed from favorites',
            'is_favorite' => false
        ]);
    }
    
    // Get all favorite medicines
    public function getFavoriteMedicines()
    {
        $user = auth()->user();
        
        $medicines = Medicine::join('favorite_medicines', 'medicines.id', '=', 'favorite_medicines.medicine_id')
            ->where('favorite_medicines.user_id', $user->id)
            ->get(['medicines.id', 'medicines.brand_name', 'medicines.generic_name', 'medicines.dosage', 'medicines.form']);
        
        return response()->json([
            'favorites' => $medicines,
            'total' => $medicines->count()
        ]);
    }
}