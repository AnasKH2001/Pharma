<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\PharmaSale;
use App\Models\User;
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

    public function getAdminStats()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        return [
            'platform_overview' => $this->getPlatformOverview(),
            'today' => $this->getAdminSalesStats($today, Carbon::now()),
            'this_week' => $this->getAdminSalesStats($startOfWeek, Carbon::now()),
            'this_month' => $this->getAdminSalesStats($startOfMonth, Carbon::now()),
            'pending_pharmacies' => $this->getPendingPharmaciesCount(),
            'recent_pharmacies' => $this->getRecentPharmacies(),
        ];
    }

    private function getPlatformOverview()
    {
        return [
            'total_pharmacies' => Pharmacy::count(),
            'active_pharmacies' => Pharmacy::where('is_active', true)->count(),
            'pending_pharmacies' => Pharmacy::where('is_active', false)->count(),
            'total_medicines' => Medicine::count(),
            'total_users' => User::count(),
            'total_sales' => PharmaSale::count(),
            'total_revenue' => PharmaSale::sum('total_price'),
        ];
    }

    private function getAdminSalesStats($startDate, $endDate)
    {
        $sales = PharmaSale::whereBetween('created_at', [$startDate, $endDate])->get();

        return [
            'sales_count' => $sales->count(),
            'total_revenue' => $sales->sum('total_price'),
            'items_sold' => $sales->sum('quantity'),
        ];
    }

    private function getPendingPharmaciesCount()
    {
        return Pharmacy::where('is_active', false)->count();
    }

    private function getRecentPharmacies()
    {
        return Pharmacy::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($pharmacy) {
                return [
                    'id' => $pharmacy->id,
                    'name' => $pharmacy->name,
                    'email' => $pharmacy->email,
                    'is_active' => $pharmacy->is_active,
                    'registered_at' => $pharmacy->created_at->diffForHumans(),
                ];
            });
    }

    public function getAdminTopPharmacies($limit = 10)
    {
        return PharmaSale::select('pharmacy_id')
            ->with('pharmacy')
            ->selectRaw('SUM(quantity) as total_items_sold, SUM(total_price) as total_revenue, COUNT(*) as total_sales')
            ->groupBy('pharmacy_id')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'pharmacy_id' => $item->pharmacy_id,
                    'pharmacy_name' => $item->pharmacy->name,
                    'is_active' => $item->pharmacy->is_active,
                    'total_sales' => (int) $item->total_sales,
                    'total_items_sold' => (int) $item->total_items_sold,
                    'total_revenue' => (int) $item->total_revenue,
                ];
            });
    }

    public function getAdminSalesTrend($days = 30)
    {
        $sales = PharmaSale::where('created_at', '>=', now()->subDays($days))
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
}
