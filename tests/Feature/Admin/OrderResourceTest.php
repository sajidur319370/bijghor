<?php

namespace Tests\Feature\Admin;

use App\Actions\CancelOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_orders_are_listed_and_can_be_filtered_by_status(): void
    {
        $confirmed = Order::factory()->paid()->create();
        $pending = Order::factory()->create();

        Livewire::test(ListOrders::class)
            ->assertCanSeeTableRecords([$confirmed, $pending])
            ->filterTable('status', 'confirmed')
            ->assertCanSeeTableRecords([$confirmed])
            ->assertCanNotSeeTableRecords([$pending]);
    }

    public function test_order_page_shows_customer_items_and_payments(): void
    {
        $order = $this->orderWithItem(Product::factory()->create(['stock' => 5]), quantity: 2);
        $order->update(['customer_name' => 'রহিম উদ্দিন', 'address' => 'বাড়ি ১২, মিরপুর']);
        $order->payments()->create(['bkash_payment_id' => 'TR001', 'trx_id' => 'BFD90JRLST', 'amount' => $order->total, 'status' => 'completed']);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertSee($order->order_number)
            ->assertSee('রহিম উদ্দিন')
            ->assertSee('বাড়ি ১২, মিরপুর')
            ->assertSee('BFD90JRLST');
    }

    public function test_orders_cannot_be_created_by_hand(): void
    {
        $this->get('/admin/orders/create')->assertNotFound();
    }

    public function test_a_paid_order_moves_confirmed_to_shipped_to_delivered(): void
    {
        $order = Order::factory()->paid()->create();

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertActionHidden('markDelivered')
            ->callAction('markShipped');
        $this->assertSame('shipped', $order->fresh()->status);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertActionHidden('markShipped')
            ->callAction('markDelivered');
        $this->assertSame('delivered', $order->fresh()->status);
    }

    public function test_unpaid_orders_cannot_be_shipped(): void
    {
        $order = Order::factory()->create();

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertActionHidden('markShipped');
    }

    public function test_cancelling_an_unpaid_order_restores_stock(): void
    {
        $product = Product::factory()->create(['stock' => 4]);
        $order = $this->orderWithItem($product, quantity: 3);

        Livewire::test(ListOrders::class)
            ->callAction(TestAction::make('cancel')->table($order));

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('unpaid', $order->fresh()->payment_status);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_cancelling_a_paid_order_marks_it_and_its_payment_refunded(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        $order = $this->orderWithItem($product, quantity: 1);
        $order->update(['payment_status' => 'paid', 'status' => 'confirmed']);
        $payment = $order->payments()->create(['bkash_payment_id' => 'DEMO-1', 'amount' => $order->total, 'status' => 'completed']);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('cancel');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('refunded', $order->fresh()->payment_status);
        $this->assertSame('refunded', $payment->fresh()->status);
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_shipped_or_delivered_orders_cannot_be_cancelled(): void
    {
        $shipped = Order::factory()->paid()->create(['status' => 'shipped']);

        Livewire::test(ViewOrder::class, ['record' => $shipped->getRouteKey()])
            ->assertActionHidden('cancel');

        $this->assertFalse(app(CancelOrder::class)->handle($shipped));
        $this->assertSame('shipped', $shipped->fresh()->status);
    }

    public function test_cancelling_twice_does_not_return_stock_twice(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        $order = $this->orderWithItem($product, quantity: 2);

        $this->assertTrue(app(CancelOrder::class)->handle($order));
        $this->assertFalse(app(CancelOrder::class)->handle($order));

        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_an_order_paid_after_auto_cancel_can_be_marked_refunded(): void
    {
        $order = Order::factory()->create(['status' => 'cancelled', 'payment_status' => 'paid']);
        $payment = $order->payments()->create(['bkash_payment_id' => 'DEMO-1', 'amount' => $order->total, 'status' => 'completed']);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertSee('refund the customer')
            ->callAction('markRefunded');

        $this->assertSame('refunded', $order->fresh()->payment_status);
        $this->assertSame('refunded', $payment->fresh()->status);
    }

    private function orderWithItem(Product $product, int $quantity): Order
    {
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

        return $order;
    }
}
