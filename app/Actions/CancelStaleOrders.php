<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Cancels bKash orders still unpaid 60 minutes after they were placed and puts their stock back
 * (docs §5.9). Orders with a payment attempt started in the last 15 minutes are left alone:
 * the customer may still be on the bKash page.
 *
 * Free hosting has no cron, so this runs when checkout or the admin dashboard loads.
 */
class CancelStaleOrders
{
    public const UNPAID_MINUTES = 60;

    public const RECENT_ATTEMPT_MINUTES = 15;

    /**
     * @return int how many orders were cancelled
     */
    public function handle(): int
    {
        return DB::transaction(function (): int {
            $orders = Order::where('status', 'pending')
                ->where('payment_status', '!=', 'paid')
                ->where('payment_method', 'bkash')
                ->where('created_at', '<', now()->subMinutes(self::UNPAID_MINUTES))
                ->whereDoesntHave('payments', fn ($query) => $query
                    ->where('status', 'initiated')
                    ->where('created_at', '>', now()->subMinutes(self::RECENT_ATTEMPT_MINUTES)))
                ->with('items')
                ->lockForUpdate()
                ->get();

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        // withTrashed: stock goes back even if the product was deleted meanwhile
                        Product::withTrashed()->whereKey($item->product_id)->increment('stock', $item->quantity);
                    }
                }

                $order->update(['status' => 'cancelled']);
            }

            return $orders->count();
        });
    }
}
