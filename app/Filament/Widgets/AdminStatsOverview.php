<?php

namespace App\Filament\Widgets;

use App\Services\StatService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stats = app(StatService::class)->getAdminStats();
        $overview = $stats['platform_overview'];
        $month    = $stats['this_month'];

        return [
            Stat::make('Active Pharmacies', $overview['active_pharmacies'])
                ->description($overview['pending_pharmacies'] . ' pending approval')
                ->color('success')
                ->icon('heroicon-o-building-storefront'),

            Stat::make('Total Medicines', $overview['total_medicines'])
                ->description('In catalog')
                ->color('info')
                ->icon('heroicon-o-beaker'),

            Stat::make('Sales This Month', number_format($month['sales_count']))
                ->description(number_format($month['items_sold']) . ' items sold')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Revenue This Month', '$' . number_format($month['total_revenue'], 2))
                ->description('All pharmacies')
                ->color('warning')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
