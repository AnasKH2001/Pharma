<?php

namespace App\Filament\Widgets;

use App\Services\StatService;
use Filament\Widgets\ChartWidget;

class SalesTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Sales Trend (Last 30 Days)';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $trend = app(StatService::class)->getAdminSalesTrend(30);

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue',
                    'data'            => array_column($trend, 'revenue'),
                    'borderColor'     => '#10b981',
                    'backgroundColor' => 'rgba(16,185,129,0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
                [
                    'label'           => 'Sales Count',
                    'data'            => array_column($trend, 'sales_count'),
                    'borderColor'     => '#6366f1',
                    'backgroundColor' => 'rgba(99,102,241,0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => array_map(
                fn ($d) => \Carbon\Carbon::parse($d['date'])->format('M d'),
                $trend
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
