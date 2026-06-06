<?php

namespace App\Services;

use App\Models\PharmaSale;
use App\Models\Inventory;
use Carbon\Carbon;

class StatService
{
    public function getDashboardStats($pharmacyId)
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        return [
            'today' => $this->getSalesStats($pharmacyId, $today, Carbon::now()),
            'this_week' => $this->getSalesStats($pharmacyId, $startOfWeek, Carbon::now()),
            'this_month' => $this->getSalesStats($pharmacyId, $startOfMonth, Carbon::now()),
            'low_stock_count' => $this->getLowStockCount($pharmacyId),
            'out_of_stock_count' => $this->getOutOfStockCount($pharmacyId),
        ];
    }

    private function getSalesStats($pharmacyId, $startDate, $endDate)
    {
        $sales = PharmaSale::where('pharmacy_id', $pharmacyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'sales_count' => $sales->count(),
            'total_revenue' => $sales->sum('total_price'),
            'items_sold' => $sales->sum('quantity'),
        ];
    }

    private function getLowStockCount($pharmacyId)
    {
        return Inventory::where('pharmacy_id', $pharmacyId)
            ->where('quantity', '>', 0)
            ->where('quantity', '<', 10)
            ->count();
    }

    private function getOutOfStockCount($pharmacyId)
    {
        return Inventory::where('pharmacy_id', $pharmacyId)
            ->where('quantity', 0)
            ->count();
    }

    public function getTopMedicines($pharmacyId, $limit = 10)
    {
        return PharmaSale::where('pharmacy_id', $pharmacyId)
            ->with('medicine')
            ->selectRaw('medicine_id, SUM(quantity) as total_quantity, SUM(total_price) as total_revenue')
            ->groupBy('medicine_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'medicine_id' => $item->medicine_id,
                    'brand_name' => $item->medicine->brand_name,
                    'generic_name' => $item->medicine->generic_name,
                    'dosage' => $item->medicine->dosage,
                    'form' => $item->medicine->form,
                    'total_quantity_sold' => (int) $item->total_quantity,
                    'total_revenue' => (int) $item->total_revenue,
                ];
            });
    }

    public function getSalesChart($pharmacyId, $days = 7)
    {
        $sales = PharmaSale::where('pharmacy_id', $pharmacyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->get()
            ->groupBy(function ($sale) {
                return $sale->created_at->format('Y-m-d');
            });

        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $daySales = $sales->get($date, collect([]));

            $chartData[] = [
                'date' => $date,
                'sales_count' => $daySales->count(),
                'items_sold' => $daySales->sum('quantity'),
                'revenue' => $daySales->sum('total_price'),
            ];
        }

        return $chartData;
    }

    public function getLowStockList($pharmacyId)
    {
        return Inventory::where('pharmacy_id', $pharmacyId)
            ->where('quantity', '>', 0)
            ->where('quantity', '<', 10)
            ->with('medicine')
            ->orderBy('quantity', 'asc')
            ->get()
            ->map(function ($inventory) {
                return [
                    'medicine_id' => $inventory->medicine_id,
                    'brand_name' => $inventory->medicine->brand_name,
                    'generic_name' => $inventory->medicine->generic_name,
                    'dosage' => $inventory->medicine->dosage,
                    'form' => $inventory->medicine->form,
                    'current_quantity' => $inventory->quantity,
                    'price' => $inventory->price,
                ];
            });
    }

    public function getOutOfStockList($pharmacyId)
    {
        return Inventory::where('pharmacy_id', $pharmacyId)
            ->where('quantity', 0)
            ->with('medicine')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($inventory) {
                return [
                    'medicine_id' => $inventory->medicine_id,
                    'brand_name' => $inventory->medicine->brand_name,
                    'generic_name' => $inventory->medicine->generic_name,
                    'dosage' => $inventory->medicine->dosage,
                    'form' => $inventory->medicine->form,
                    'price' => $inventory->price,
                    'out_since' => $inventory->updated_at->diffForHumans(),
                ];
            });
    }
}
