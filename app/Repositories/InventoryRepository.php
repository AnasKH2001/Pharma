<?php

namespace App\Repositories;

use App\Models\Inventory;

class InventoryRepository
{
    public function updateOrCreate($pharmacyId, $medicineId, $quantity, $price)
    {
        return Inventory::updateOrCreate(
            [
                'pharmacy_id' => $pharmacyId,
                'medicine_id' => $medicineId
            ],
            [
                'quantity' => $quantity,
                'price' => $price
            ]
        );
    }

    public function getByPharmacy($pharmacyId)
    {
        return Inventory::with('medicine')
            ->where('pharmacy_id', $pharmacyId)
            ->get();
    }

    public function decreaseStock($pharmacyId, $medicineId, $quantity)
    {
        $inventory = $this->getByPharmacyAndMedicine($pharmacyId, $medicineId);
        
        if ($inventory) {
            $inventory->decrement('quantity', $quantity);
            $inventory->refresh();
        }
        
        return $inventory;
    }

    public function getByPharmacyAndMedicine($pharmacyId, $medicineId)
    {
        return Inventory::where('pharmacy_id', $pharmacyId)
            ->where('medicine_id', $medicineId)
            ->first();
    }
}