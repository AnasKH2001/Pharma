<?php

namespace App\Services;

use App\Imports\InventoryImport;
use App\Repositories\InventoryRepository;
use Maatwebsite\Excel\Facades\Excel;

class InventoryService
{
    protected InventoryRepository $inventoryRepository;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    public function uploadInventory($file, $pharmacyId)
    {
        $import = new InventoryImport($pharmacyId, $this->inventoryRepository);
        Excel::import($import, $file);

        return [
            'added' => $import->getAddedCount(),
            'skipped' => $import->getSkippedCount(),
            'errors' => $import->getErrors(),
        ];
    }

        public function getPharmacyInventory($pharmacyId, $perPage = 15)
        {
            return $this->inventoryRepository->getByPharmacy($pharmacyId, $perPage);
        }
}
