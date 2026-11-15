<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;

/**
 * Daily sales for the last 30 days (docs §5.11). Days without sales show as 0.
 */
class SalesChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Sales, last 30 days';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $from = today()->subDays(29);

        $revenueByDay = Order::where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('paid_at', '>=', $from)
            ->selectRaw('DATE(paid_at) as day, SUM(total) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $days = collect(CarbonPeriod::create($from, today()));

        return [
            'datasets' => [
                [
                    'label' => 'Sales (৳)',
                    'data' => $days->map(fn ($day): float => (float) ($revenueByDay[$day->toDateString()] ?? 0))->all(),
                    'borderColor' => '#2f7d32',
                    'backgroundColor' => 'rgba(47, 125, 50, 0.15)',
                    'fill' => true,
                ],
            ],
            'labels' => $days->map(fn ($day): string => $day->format('d M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
