<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Services\StatService;
use Illuminate\Http\Request;


class StatController extends Controller
{
    protected StatService $statService;

    public function __construct(StatService $statService)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('pharmacy.approved');
        $this->statService = $statService;
    }

    public function dashboard()
    {
        $user = auth()->user();

        $pharmacy = Pharmacy::where('email', $user->email)->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $stats = $this->statService->getDashboardStats($pharmacy->id);

        return response()->json($stats);
    }

    public function topMedicines(Request $request)
    {
        $user = auth()->user();

        $pharmacy = Pharmacy::where('email', $user->email)->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $limit = $request->get('limit', 10);
        $topMedicines = $this->statService->getTopMedicines($pharmacy->id, $limit);

        return response()->json([
            'top_medicines' => $topMedicines,
            'total' => $topMedicines->count()
        ]);
    }

    public function salesChart(Request $request)
    {
        $user = auth()->user();

        $pharmacy = Pharmacy::where('email', $user->email)->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $days = $request->get('days', 7);
        $chartData = $this->statService->getSalesChart($pharmacy->id, $days);

        return response()->json([
            'chart' => $chartData,
            'days' => $days
        ]);
    }

    public function lowStock()
    {
        $user = auth()->user();

        $pharmacy = Pharmacy::where('email', $user->email)->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        $lowStock = $this->statService->getLowStockList($pharmacy->id);
        $outOfStock = $this->statService->getOutOfStockList($pharmacy->id);

        return response()->json([
            'low_stock' => $lowStock,
            'low_stock_count' => $lowStock->count(),
            'out_of_stock' => $outOfStock,
            'out_of_stock_count' => $outOfStock->count(),
        ]);
    }
}
