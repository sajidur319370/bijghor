<?php

namespace Tests\Feature;

use App\Actions\CancelStaleOrders;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelStaleOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_unpaid_for_an_hour_are_cancelled_and_stock_returns(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $order = $this->orderPlacedMinutesAgo(61, $product, quantity: 3);

        $this->assertSame(1, app(CancelStaleOrders::class)->handle());

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_stock_returns_even_if_the_product_was_deleted_meanwhile(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        $this->orderPlacedMinutesAgo(90, $product, quantity: 2);
        $product->delete();

        app(CancelStaleOrders::class)->handle();

        $this->assertSame(2, Product::withTrashed()->find($product->id)->stock);
    }

    public function test_recent_orders_are_left_alone(): void
    {
        $order = $this->orderPlacedMinutesAgo(30, Product::factory()->create());

        $this->assertSame(0, app(CancelStaleOrders::class)->handle());
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_an_order_with_a_payment_started_in_the_last_15_minutes_is_left_alone(): void
    {
        $order = $this->orderPlacedMinutesAgo(70, Product::factory()->create());
        $this->travel(-5)->minutes();
        $order->payments()->create(['bkash_payment_id' => 'TR-RECENT', 'amount' => $order->total]);
        $this->travelBack();

        $this->assertSame(0, app(CancelStaleOrders::class)->handle());
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_an_old_abandoned_payment_attempt_does_not_protect_the_order(): void
    {
        $order = $this->orderPlacedMinutesAgo(70, Product::factory()->create());
        $this->travel(-20)->minutes();
        $order->payments()->create(['bkash_payment_id' => 'TR-OLD', 'amount' => $order->total]);
        $this->travelBack();

        $this->assertSame(1, app(CancelStaleOrders::class)->handle());
    }

    public function test_orders_whose_payment_failed_are_cancelled_too(): void
    {
        $order = $this->orderPlacedMinutesAgo(61, Product::factory()->create());
        $order->update(['payment_status' => 'failed']);

        app(CancelStaleOrders::class)->handle();

        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_paid_and_cash_on_delivery_orders_are_never_cancelled(): void
    {
        $paid = $this->orderPlacedMinutesAgo(120, Product::factory()->create());
        $paid->update(['payment_status' => 'paid', 'status' => 'confirmed']);
        $cod = $this->orderPlacedMinutesAgo(120, Product::factory()->create());
        $cod->update(['payment_method' => 'cod']);

        $this->assertSame(0, app(CancelStaleOrders::class)->handle());
    }

    public function test_opening_checkout_runs_the_cleanup(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        $stale = $this->orderPlacedMinutesAgo(61, $product, quantity: 1);

        $this->withSession(['cart' => [$product->id => 1]])->get(route('checkout'));

        $this->assertSame('cancelled', $stale->fresh()->status);
        $this->assertSame(1, $product->fresh()->stock);
    }

    private function orderPlacedMinutesAgo(int $minutes, Product $product, int $quantity = 1): Order
    {
        $this->travel(-$minutes)->minutes();

        $order = Order::factory()->create();
        $order->items()->create([
            'product_id' => $product->id,
            'product_name_en' => $product->name_en,
            'product_name_bn' => $product->name_bn,
            'packet_size_en' => $product->packet_size_en,
            'packet_size_bn' => $product->packet_size_bn,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'line_total' => $product->price * $quantity,
        ]);

        $this->travelBack();

        return $order;
    }
}
