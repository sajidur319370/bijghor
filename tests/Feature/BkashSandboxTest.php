<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Payments\BkashSandbox;
use App\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class BkashSandboxTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'https://sandbox.test/v1.2.0-beta/tokenized/checkout';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.payment.driver' => 'bkash_sandbox',
            'services.bkash' => [
                'base_url' => self::BASE,
                'app_key' => 'test-app-key',
                'app_secret' => 'test-app-secret',
                'username' => 'test-user',
                'password' => 'test-pass',
            ],
        ]);

        Http::preventStrayRequests();
    }

    public function test_sandbox_driver_is_used_when_configured(): void
    {
        $this->assertInstanceOf(BkashSandbox::class, app(PaymentGateway::class));
    }

    public function test_create_sends_the_order_and_reuses_the_cached_token(): void
    {
        Http::fake([
            self::BASE.'/token/grant' => Http::response(['id_token' => 'TOKEN-1', 'expires_in' => 3600]),
            self::BASE.'/create' => Http::response(['paymentID' => 'TR001', 'bkashURL' => 'https://sandbox.bka.sh/pay/TR001', 'statusCode' => '0000']),
        ]);
        $order = Order::factory()->create(['total' => '458.00']);
        $gateway = app(PaymentGateway::class);

        $first = $gateway->create($order);
        $gateway->create($order);

        $this->assertSame('TR001', $first['paymentID']);
        Http::assertSentCount(3);   // 1 token + 2 create: the token was cached
        Http::assertSent(fn (Request $request) => $request->url() === self::BASE.'/token/grant'
            && $request->hasHeader('username', 'test-user')
            && $request['app_secret'] === 'test-app-secret');
        Http::assertSent(fn (Request $request) => $request->url() === self::BASE.'/create'
            && $request->hasHeader('Authorization', 'TOKEN-1')
            && $request->hasHeader('X-APP-Key', 'test-app-key')
            && $request['amount'] === '458.00'
            && $request['currency'] === 'BDT'
            && $request['intent'] === 'sale'
            && $request['merchantInvoiceNumber'] === $order->order_number
            && $request['callbackURL'] === route('bkash.callback'));
    }

    public function test_a_failed_token_grant_throws(): void
    {
        Http::fake([self::BASE.'/token/grant' => Http::response(['statusCode' => '2079', 'statusMessage' => 'Invalid app token'])]);

        $this->expectException(RuntimeException::class);
        app(PaymentGateway::class)->execute('TR001');
    }

    public function test_a_rejected_token_is_dropped_so_the_next_call_gets_a_new_one(): void
    {
        Http::fake([
            self::BASE.'/token/grant' => Http::sequence()
                ->push(['id_token' => 'OLD'])
                ->push(['id_token' => 'NEW']),
            self::BASE.'/execute' => Http::sequence()
                ->push(['message' => 'Unauthorized'], 401)
                ->push(['transactionStatus' => 'Completed']),
        ]);
        $gateway = app(PaymentGateway::class);

        $gateway->execute('TR001');
        $gateway->execute('TR001');

        Http::assertSent(fn (Request $request) => $request->url() === self::BASE.'/execute' && $request->hasHeader('Authorization', 'NEW'));
    }

    public function test_full_payment_through_the_sandbox_confirms_the_order(): void
    {
        Http::fake([
            self::BASE.'/token/grant' => Http::response(['id_token' => 'TOKEN-1']),
            self::BASE.'/create' => Http::response(['paymentID' => 'TR001', 'bkashURL' => 'https://sandbox.bka.sh/pay/TR001', 'statusCode' => '0000']),
            self::BASE.'/execute' => Http::response([
                'paymentID' => 'TR001', 'trxID' => 'BFD90JRLST', 'transactionStatus' => 'Completed',
                'amount' => '458.00', 'currency' => 'BDT', 'customerMsisdn' => '01770618575', 'statusCode' => '0000',
            ]),
        ]);
        $order = Order::factory()->create(['total' => '458.00']);

        $this->get(route('order.pay', $order->order_number))
            ->assertRedirect('https://sandbox.bka.sh/pay/TR001');

        $this->get(route('bkash.callback', ['paymentID' => 'TR001', 'status' => 'success']))
            ->assertRedirect(route('checkout.success', $order->order_number));

        $payment = $order->payments()->sole();
        $this->assertSame('completed', $payment->status);
        $this->assertSame('BFD90JRLST', $payment->trx_id);
        $this->assertSame('01770618575', $payment->payer_account);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_execute_timeout_falls_back_to_payment_status(): void
    {
        Http::fake([
            self::BASE.'/token/grant' => Http::response(['id_token' => 'TOKEN-1']),
            self::BASE.'/execute' => Http::failedConnection(),
            self::BASE.'/payment/status' => Http::response([
                'paymentID' => 'TR001', 'trxID' => 'BFD90JRLST', 'transactionStatus' => 'Completed', 'amount' => '458.00',
            ]),
        ]);
        $order = Order::factory()->create(['total' => '458.00']);
        $order->payments()->create(['bkash_payment_id' => 'TR001', 'amount' => '458.00']);

        $this->get(route('bkash.callback', ['paymentID' => 'TR001', 'status' => 'success']))
            ->assertRedirect(route('checkout.success', $order->order_number));

        Http::assertSent(fn (Request $request) => $request->url() === self::BASE.'/payment/status');
    }

    public function test_bkash_unreachable_everywhere_leaves_the_payment_open(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $order = Order::factory()->create();
        $payment = $order->payments()->create(['bkash_payment_id' => 'TR001', 'amount' => $order->total]);

        $this->get(route('bkash.callback', ['paymentID' => 'TR001', 'status' => 'success']))
            ->assertRedirect(route('checkout.failed', $order->order_number));

        $this->assertSame('initiated', $payment->fresh()->status);
    }

    public function test_bkash_unreachable_when_starting_payment_shows_retry_page(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $order = Order::factory()->create();

        $this->get(route('order.pay', $order->order_number))
            ->assertRedirect(route('checkout.failed', $order->order_number))
            ->assertSessionHas('error');
    }
}
