<?php

namespace App\Filament\Pharmacy\Concerns;

use App\Models\Pharmacy;

trait HasCurrentPharmacy
{
    public static function getCurrentPharmacy(): Pharmacy
    {
        return Pharmacy::where('email', auth()->user()->email)->firstOrFail();
    }
}
