<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Admin cancels an order (docs §5.11): stock goes back, and a (test-)paid order is marked refunded.
 * Shipped, delivered and already-cancelled orders cannot be cancelled.
 */
class CancelOrder
{
    /** @var list<string> */
    public const CANCELLABLE_STATUSES = ['pending', 'confirmed'];

    /**
     * @return bool false when the order could no longer be cancelled (someone changed it meanwhile)
     */
    public function handle(Order $order): bool
    {
        return DB::transaction(function () use ($order): bool {
            $order = Order::with('items')->whereKey($order->id)->lockForUpdate()->first();

            if (! in_array($order->status, self::CANCELLABLE_STATUSES, true)) {
                return false;
            }

            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::withTrashed()->whereKey($item->product_id)->increment('stock', $item->quantity);
                }
            }

            $wasPaid = $order->payment_status === 'paid';

            $order->update([
                'status' => 'cancelled',
                'payment_status' => $wasPaid ? 'refunded' : $order->payment_status,
            ]);

            if ($wasPaid) {
                // Demo / sandbox money only: record the refund (no real money moves in V1)
                $order->payments()->where('status', 'completed')->update(['status' => 'refunded']);
            }

            return true;
        });
    }
}
