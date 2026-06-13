<?php

namespace App\Filament\Pharmacy\Widgets;

use App\Filament\Pharmacy\Concerns\HasCurrentPharmacy;
use App\Services\StatService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PharmacyStatsOverview extends BaseWidget
{
    use HasCurrentPharmacy;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pharmacy = self::getCurrentPharmacy();
        $stats    = app(StatService::class)->getDashboardStats($pharmacy->id);

        $today = $stats['today'];
        $month = $stats['this_month'];

        return [
            Stat::make('Sales Today', $today['sales_count'])
                ->description('Revenue: $' . number_format($today['total_revenue'], 2))
                ->color('success')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('This Month', $month['sales_count'] . ' sales')
                ->description('$' . number_format($month['total_revenue'], 2) . ' revenue')
                ->color('primary')
                ->icon('heroicon-o-calendar'),

            Stat::make('Low Stock', $stats['low_stock_count'])
                ->description($stats['out_of_stock_count'] . ' out of stock')
                ->color($stats['low_stock_count'] > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Items Sold Today', $today['items_sold'])
                ->description('This month: ' . $month['items_sold'])
                ->color('info')
                ->icon('heroicon-o-archive-box'),
        ];
    }
}
