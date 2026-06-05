<?php

namespace App\Services;

use App\Repositories\SaleRepository;
use App\Repositories\InventoryRepository;
use App\Models\LowStockNotification;

class SaleService
{
    protected SaleRepository $saleRepository;
    protected InventoryRepository $inventoryRepository;
    
    public function __construct(
        SaleRepository $saleRepository,
        InventoryRepository $inventoryRepository
    ) {
        $this->saleRepository = $saleRepository;
        $this->inventoryRepository = $inventoryRepository;
    }

    public function recordSale($pharmacyId, $medicineId, $quantity)
    {
        // Get inventory to get the price
        $inventory = $this->inventoryRepository->getByPharmacyAndMedicine($pharmacyId, $medicineId);

        if (!$inventory) {
            throw new \Exception('Medicine not found in inventory');
        }

        if ($inventory->quantity < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        $unitPrice = $inventory->price;
        $totalPrice = $quantity * $unitPrice;

        // Create sale record
        $sale = $this->saleRepository->create([
            'pharmacy_id' => $pharmacyId,
            'medicine_id' => $medicineId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
        ]);

        // Decrease inventory
        $inventory = $this->inventoryRepository->decreaseStock($pharmacyId, $medicineId, $quantity);

        // Check if stock reached zero
        if ($inventory && $inventory->quantity === 0) {
            LowStockNotification::create([
                'pharmacy_id' => $pharmacyId,
                'medicine_id' => $medicineId,
            ]);
        }

        return [
            'sale' => $sale->load('medicine'),
            'remaining_stock' => $inventory ? $inventory->quantity : 0
        ];
    }
    
    public function getSalesHistory($pharmacyId)
    {
        return $this->saleRepository->getByPharmacy($pharmacyId);
    }
}