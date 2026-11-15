<?php

namespace App\Filament\Pages;

use App\Actions\CancelStaleOrders;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Free hosting has no cron, so unpaid orders are cleaned up whenever the dashboard opens (docs §5.9).
     */
    public function mount(CancelStaleOrders $cancelStaleOrders): void
    {
        $cancelStaleOrders->handle();
    }
}
