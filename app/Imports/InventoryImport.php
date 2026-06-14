<?php

namespace App\Imports;

use App\Models\Medicine;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InventoryImport implements ToModel, WithHeadingRow
{
    private $pharmacyId;
    private $inventoryRepository;
    private $addedCount = 0;
    private $skippedCount = 0;
    private $errors = [];

    public function __construct($pharmacyId, $inventoryRepository)
    {
        $this->pharmacyId = $pharmacyId;
        $this->inventoryRepository = $inventoryRepository;
    }

    public function model(array $row)
    {
        // Find medicine by name combination
        $medicine = Medicine::where('brand_name', $row['brand_name'])
            ->where('generic_name', $row['generic_name'])
            ->where('dosage', $row['dosage'])
            ->where('form', $row['form'])
            ->first();

        if (!$medicine) {
            $this->skippedCount++;
            $this->errors[] = "Medicine not found: {$row['brand_name']} - {$row['generic_name']} ({$row['dosage']}, {$row['form']})";
            return null;
        }

        $this->addedCount++;

        return $this->inventoryRepository->updateOrCreate(
            $this->pharmacyId,
            $medicine->id,
            $row['quantity'],
            $row['price']
        );
    }

    public function getAddedCount(): int
    {
        return $this->addedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
