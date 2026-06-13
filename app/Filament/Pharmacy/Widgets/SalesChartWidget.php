<?php

namespace App\Filament\Pharmacy\Widgets;

use App\Filament\Pharmacy\Concerns\HasCurrentPharmacy;
use App\Services\StatService;
use Filament\Widgets\ChartWidget;

class SalesChartWidget extends ChartWidget
{
    use HasCurrentPharmacy;

    protected static ?string $heading = 'Sales Last 7 Days';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $pharmacy = self::getCurrentPharmacy();
        $chart    = app(StatService::class)->getSalesChart($pharmacy->id, 7);

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue',
                    'data'            => array_column($chart, 'revenue'),
                    'borderColor'     => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
                [
                    'label'           => 'Items Sold',
                    'data'            => array_column($chart, 'items_sold'),
                    'borderColor'     => '#10b981',
                    'backgroundColor' => 'rgba(16,185,129,0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => array_map(
                fn ($d) => \Carbon\Carbon::parse($d['date'])->format('D d'),
                $chart
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
