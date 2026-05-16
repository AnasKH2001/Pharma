<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PharmaRegisterRequest;
use App\Services\PharmacyService;

class PharmaRegisterController extends Controller
{
    protected PharmacyService $pharmacyService;
    
    public function __construct(PharmacyService $pharmacyService)
    {
        $this->pharmacyService = $pharmacyService;
    }
    
    public function register(PharmaRegisterRequest $request)
    {
        $result = $this->pharmacyService->register(
            $request->validated(),
            $request->file('credentials')
        );
        
        return response()->json($result, 201);
    }
}