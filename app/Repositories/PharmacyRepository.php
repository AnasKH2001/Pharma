<?php

namespace App\Repositories;

use App\Models\Pharmacy;

class PharmacyRepository
{
    public function create(array $data): Pharmacy
    {
        return Pharmacy::create($data);
    }
}