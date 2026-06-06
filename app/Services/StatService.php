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
}