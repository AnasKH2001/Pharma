<?php

namespace App\Repositories;

use App\Models\PharmaSale;

class SaleRepository
{
    public function create(array $data): PharmaSale
    {
        return PharmaSale::create($data);
    }

    public function getByPharmacy($pharmacyId, $perPage = 15)
    {
        return PharmaSale::with('medicine')
            ->where('pharmacy_id', $pharmacyId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
