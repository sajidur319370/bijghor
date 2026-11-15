<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Today at a glance (docs §5.11). Sales = paid orders that were not cancelled afterwards.
 */
class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $ordersToday = Order::whereDate('created_at', today())->count();

        $salesToday = (float) Order::where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->whereDate('paid_at', today())
            ->sum('total');

        $waitingToShip = Order::where('status', 'confirmed')->count();

        $needsRefund = Order::where('status', 'cancelled')->where('payment_status', 'paid')->count();

        $stats = [
            Stat::make("Today's orders", $ordersToday)->icon('heroicon-o-shopping-cart'),
            Stat::make("Today's sales", '৳ '.number_format($salesToday))->icon('heroicon-o-banknotes')->color('success'),
            Stat::make('Waiting to ship', $waitingToShip)
                ->description('Paid, not shipped yet')
                ->icon('heroicon-o-truck')
                ->color($waitingToShip > 0 ? 'warning' : 'gray')
                ->url(OrderResource::getUrl('index', ['filters' => ['status' => ['value' => 'confirmed']]])),
        ];

        if ($needsRefund > 0) {
            $stats[] = Stat::make('Needs refund', $needsRefund)
                ->description('Paid after the order was cancelled')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger');
        }

        return $stats;
    }
}
