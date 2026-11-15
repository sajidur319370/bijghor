<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Payments\DemoGateway;
use App\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoPaymentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_driver_is_used_when_configured(): void
    {
        config(['services.payment.driver' => 'demo']);

        $this->assertInstanceOf(DemoGateway::class, app(PaymentGateway::class));
    }

    public function test_demo_page_shows_order_amount_and_three_choices(): void
    {
        $order = Order::factory()->create();
        $order->payments()->create(['bkash_payment_id' => 'DEMO-ABC123', 'amount' => $order->total]);

        $this->get(route('demo-pay.show', 'DEMO-ABC123'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('৳ ৪৫৮')
            ->assertSee(e(route('bkash.callback', ['paymentID' => 'DEMO-ABC123', 'status' => 'success'])), false)
            ->assertSee(e(route('bkash.callback', ['paymentID' => 'DEMO-ABC123', 'status' => 'failure'])), false)
            ->assertSee(e(route('bkash.callback', ['paymentID' => 'DEMO-ABC123', 'status' => 'cancel'])), false);
    }

    public function test_demo_page_is_gone_when_the_driver_is_not_demo(): void
    {
        $order = Order::factory()->create();
        $order->payments()->create(['bkash_payment_id' => 'DEMO-ABC123', 'amount' => $order->total]);
        config(['services.payment.driver' => 'bkash_sandbox']);

        $this->get(route('demo-pay.show', 'DEMO-ABC123'))->assertNotFound();
    }

    public function test_finished_attempts_cannot_be_paid_again_on_the_demo_page(): void
    {
        $order = Order::factory()->create();
        $order->payments()->create(['bkash_payment_id' => 'DEMO-DONE', 'amount' => $order->total, 'status' => 'completed']);

        $this->get(route('demo-pay.show', 'DEMO-DONE'))->assertNotFound();
    }
}
