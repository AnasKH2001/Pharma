<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Pharmacy;

class PharmacyApprovedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // Check if user is a pharmacy
        if ($user->role !== 'pharmacy') {
            return response()->json([
                'message' => 'Access denied. Pharmacy only.'
            ], 403);
        }
        
        // Check if pharmacy is approved
        $pharmacy = Pharmacy::where('email', $user->email)->first();
        
        if (!$pharmacy || !$pharmacy->is_active) {
            return response()->json([
                'message' => 'Your pharmacy account is pending admin approval'
            ], 403);
        }
        
        return $next($request);
    }
}