<?php

namespace App\Services;

use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Turns the cart into an order (docs §5.7).
 *
 * Product rows are locked so two customers cannot buy the last packet at the same time.
 * Stock is reduced now; unpaid orders are cancelled later by CancelStaleOrders.
 * Money is calculated in paisa (integers) so totals never suffer float rounding.
 */
class PlaceOrder
{
    /** One phone number may hold at most this many unpaid orders. */
    public const MAX_OPEN_ORDERS_PER_PHONE = 3;

    /**
     * @param  array{name: string, phone: string, email?: string|null, delivery_zone_id: int|string, district: string, address: string, note?: string|null}  $data
     *
     * @throws ValidationException when the cart is empty, stock ran out, or the phone has too many unpaid orders
     */
    public function handle(array $data, Cart $cart): Order
    {
        $items = $cart->items();

        if ($items === []) {
            throw ValidationException::withMessages(['cart' => __('Cart is empty')]);
        }

        $openOrders = Order::where('customer_phone', $data['phone'])->where('status', 'pending')->count();
        if ($openOrders >= self::MAX_OPEN_ORDERS_PER_PHONE) {
            throw ValidationException::withMessages([
                'phone' => __('You already have unpaid orders. Please pay for them or wait for them to expire.'),
            ]);
        }

        $order = DB::transaction(function () use ($data, $items): Order {
            // ORDER BY id: every checkout locks rows in the same order, which avoids deadlocks
            $products = Product::active()
                ->whereIn('id', array_keys($items))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotalPaisa = 0;
            foreach ($items as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product || $product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => __(':name is out of stock', ['name' => $product?->name ?? '#'.$productId]),
                    ]);
                }

                $subtotalPaisa += self::paisa($product->effective_price) * $quantity;
            }

            $zone = DeliveryZone::active()->findOrFail($data['delivery_zone_id']);
            $chargePaisa = self::paisa($zone->charge);

            $order = Order::create([
                'order_number' => Order::newOrderNumber(),
                'customer_name' => $data['name'],
                'customer_phone' => $data['phone'],
                'customer_email' => $data['email'] ?? null,
                'delivery_zone_id' => $zone->id,
                'district' => $data['district'],
                'address' => $data['address'],
                'note' => $data['note'] ?? null,
                'subtotal' => self::taka($subtotalPaisa),
                'delivery_charge' => self::taka($chargePaisa),
                'total' => self::taka($subtotalPaisa + $chargePaisa),
                'payment_method' => 'bkash',
                'locale' => app()->getLocale(),
            ]);

            foreach ($items as $productId => $quantity) {
                $product = $products[$productId];
                $unitPaisa = self::paisa($product->effective_price);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name_en' => $product->name_en,
                    'product_name_bn' => $product->name_bn,
                    'packet_size_en' => $product->packet_size_en,
                    'packet_size_bn' => $product->packet_size_bn,
                    'unit_price' => self::taka($unitPaisa),
                    'quantity' => $quantity,
                    'line_total' => self::taka($unitPaisa * $quantity),
                ]);

                $product->decrement('stock', $quantity);
            }

            return $order;
        });

        $cart->clear();

        return $order;
    }

    private static function paisa(string|int|float $taka): int
    {
        return (int) round((float) $taka * 100);
    }

    private static function taka(int $paisa): string
    {
        return number_format($paisa / 100, 2, '.', '');
    }
}
