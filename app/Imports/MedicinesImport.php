<?php

namespace App\Imports;

use App\Models\Medicine;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MedicinesImport implements ToModel, WithHeadingRow
{
    private $addedCount = 0;
    private $skippedCount = 0;
    
    public function model(array $row)
    {
        $exists = Medicine::where('brand_name', $row['brand_name'])
            ->where('manufacturer', $row['manufacturer'])
            ->where('generic_name', $row['generic_name'])
            ->where('dosage', $row['dosage'])
            ->where('form', $row['form'])
            ->exists();
        
        if ($exists) {
            $this->skippedCount++;
            return null; // Skip duplicate
        }
        
        $this->addedCount++;
        
        return new Medicine([
            'brand_name' => $row['brand_name'],
            'manufacturer' => $row['manufacturer'],
            'generic_name' => $row['generic_name'],
            'dosage' => $row['dosage'],
            'form' => $row['form'],
        ]);
    }
    
    public function getAddedCount(): int
    {
        return $this->addedCount;
    }
    
    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}