<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Payments\PaymentGateway;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * The payment rules from docs §5.8, end to end through the HTTP routes.
 */
class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_paying_on_the_demo_page_confirms_the_order(): void
    {
        $order = Order::factory()->create();

        $payUrl = $this->get(route('order.pay', $order->order_number))->assertRedirect()->headers->get('Location');
        $payment = $order->payments()->sole();
        $this->assertSame('initiated', $payment->status);
        $this->assertSame(route('demo-pay.show', $payment->bkash_payment_id), $payUrl);

        $this->get($this->callbackUrl($payment, 'success'))
            ->assertRedirect(route('checkout.success', $order->order_number));

        $payment->refresh();
        $order->refresh();
        $this->assertSame('completed', $payment->status);
        $this->assertStringStartsWith('DEMO', $payment->trx_id);
        $this->assertNotNull($payment->completed_at);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->status);
        $this->assertNotNull($order->paid_at);
    }

    public function test_cancelling_keeps_the_order_open_and_paying_again_works(): void
    {
        $order = Order::factory()->create();
        $this->get(route('order.pay', $order->order_number));
        $first = $order->payments()->sole();

        $this->get($this->callbackUrl($first, 'cancel'))
            ->assertRedirect(route('checkout.failed', $order->order_number));

        $this->assertSame('cancelled', $first->fresh()->status);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame('failed', $order->fresh()->payment_status);

        $this->get(route('checkout.failed', $order->order_number))
            ->assertSee(route('order.pay', $order->order_number), false);   // "Try again"

        $this->get(route('order.pay', $order->order_number))->assertRedirect();
        $second = $order->payments()->latest('id')->first();
        $this->assertNotSame($first->id, $second->id);

        $this->get($this->callbackUrl($second, 'success'))
            ->assertRedirect(route('checkout.success', $order->order_number));
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_failure_marks_the_attempt_failed(): void
    {
        $order = Order::factory()->create();
        $payment = $order->payments()->create(['bkash_payment_id' => 'DEMO-1', 'amount' => $order->total]);

        $this->get($this->callbackUrl($payment, 'failure'))
            ->assertRedirect(route('checkout.failed', $order->order_number));

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_refreshing_the_callback_after_success_changes_nothing_and_does_not_call_the_gateway_again(): void
    {
        $order = Order::factory()->create();
        $payment = $order->payments()->create(['bkash_payment_id' => 'FAKE-1', 'amount' => $order->total]);
        $gateway = $this->fakeGateway(execute: fn () => $this->completed('458.00'));

        $this->get($this->callbackUrl($payment, 'success'))->assertRedirect(route('checkout.success', $order->order_number));
        $trxId = $payment->fresh()->trx_id;

        $this->get($this->callbackUrl($payment, 'success'))->assertRedirect(route('checkout.success', $order->order_number));

        $this->assertSame(1, $gateway->executeCalls);
        $this->assertSame($trxId, $payment->fresh()->trx_id);
        $this->assertSame(1, Payment::where('status', 'completed')->count());
    }

    public function test_a_fake_cancel_link_cannot_undo_a_completed_payment(): void
    {
        $order = Order::factory()->create();
        $payment = $order->payments()->create(['bkash_payment_id' => 'DEMO-1', 'amount' => $order->total]);
        $this->get($this->callbackUrl($payment, 'success'));

        $this->get($this->callbackUrl($payment, 'cancel'))
            ->assertRedirect(route('checkout.success', $order->order_number));

        $this->assertSame('completed', $payment->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_a_payment_that_arrives_after_auto_cancel_is_flagged_for_refund(): void
    {
        $order = Order::factory()->create();
        $payment = $order->payments()->create(['bkash_payment_id' => 'DEMO-1', 'amount' => $order->total]);
        $order->update(['status' => 'cancelled']);   // auto-cancel ran while the customer was paying

        $this->get($this->callbackUrl($payment, 'success'))
            ->assertRedirect(route('checkout.failed', $order->order_number))
            ->assertSessionHas('error', 'পেমেন্ট শেষ হওয়ার আগেই অর্ডারটি বাতিল হয়ে গেছে। আপনার টাকা ফেরত দেওয়া হবে।');

        $this->assertSame('completed', $payment->fresh()->status);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_no_clear_answer_from_the_gateway_leaves_the_payment_open(): void
    {
        $order = Order::factory()->create();
        $payment = $order->payments()->create(['bkash_payment_id' => 'FAKE-1', 'amount' => $order->total]);
        $this->fakeGateway(
            execute: fn () => throw new RuntimeException('cURL error 28: timed out'),
            status: fn () => ['statusCode' => '9999', 'statusMessage' => 'System error'],
        );

        $this->get($this->callbackUrl($payment, 'success'))
            ->assertRedirect(route('checkout.failed', $order->order_number))
            ->assertSessionHas('error');

        $this->assertSame('initiated', $payment->fresh()->status);
        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }

    public function test_when_execute_times_out_the_status_answer_decides(): void
    {
        $order = Order::factory()->create();
        $payment = $order->payments()->create(['bkash_payment_id' => 'FAKE-1', 'amount' => $order->total]);
        $this->fakeGateway(
            execute: fn () => throw new RuntimeException('timed out'),
            status: fn () => $this->completed('458.00'),
        );

        $this->get($this->callbackUrl($payment, 'success'))
            ->assertRedirect(route('checkout.success', $order->order_number));

        $this->assertSame('completed', $payment->fresh()->status);
    }

    public function test_a_wrong_amount_is_not_accepted(): void
    {
        $order = Order::factory()->create();   // total 458.00
        $payment = $order->payments()->create(['bkash_payment_id' => 'FAKE-1', 'amount' => $order->total]);
        $this->fakeGateway(execute: fn () => $this->completed('1.00'));

        $this->get($this->callbackUrl($payment, 'success'))
            ->assertRedirect(route('checkout.failed', $order->order_number));

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->trx_id);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_paid_or_cancelled_orders_cannot_start_a_new_payment(): void
    {
        $paid = Order::factory()->paid()->create();
        $cancelled = Order::factory()->cancelled()->create();

        $this->get(route('order.pay', $paid->order_number))->assertNotFound();
        $this->get(route('order.pay', $cancelled->order_number))->assertNotFound();
    }

    public function test_gateway_down_when_starting_payment_shows_a_retry_page(): void
    {
        $order = Order::factory()->create();
        $this->app->instance(PaymentGateway::class, new class implements PaymentGateway
        {
            public function create(Order $order): array
            {
                throw new RuntimeException('Could not resolve host');
            }

            public function execute(string $paymentId): array
            {
                return [];
            }

            public function status(string $paymentId): array
            {
                return [];
            }
        });

        $this->get(route('order.pay', $order->order_number))
            ->assertRedirect(route('checkout.failed', $order->order_number))
            ->assertSessionHas('error', 'বিকাশ এখন পাওয়া যাচ্ছে না, আবার চেষ্টা করুন');

        $this->assertSame(0, $order->payments()->count());
    }

    public function test_success_page_needs_a_paid_order_and_shows_no_personal_details(): void
    {
        $unpaid = Order::factory()->create();
        $this->get(route('checkout.success', $unpaid->order_number))
            ->assertRedirect(route('checkout.failed', $unpaid->order_number));

        $paid = Order::factory()->paid()->create(['customer_phone' => '01799999999', 'address' => 'গোপন ঠিকানা']);
        $this->get(route('checkout.success', $paid->order_number))
            ->assertOk()
            ->assertSee($paid->order_number)
            ->assertDontSee('01799999999')
            ->assertDontSee('গোপন ঠিকানা');
    }

    public function test_unknown_payment_id_is_not_found(): void
    {
        $this->get(route('bkash.callback', ['paymentID' => 'NOPE', 'status' => 'success']))->assertNotFound();
        $this->get(route('bkash.callback'))->assertNotFound();
    }

    private function callbackUrl(Payment $payment, string $status): string
    {
        return route('bkash.callback', ['paymentID' => $payment->bkash_payment_id, 'status' => $status]);
    }

    /**
     * @return array<string, string>
     */
    private function completed(string $amount): array
    {
        return [
            'paymentID' => 'FAKE-1',
            'trxID' => 'TRX'.random_int(1000, 9999),
            'transactionStatus' => 'Completed',
            'amount' => $amount,
            'customerMsisdn' => '01770618575',
        ];
    }

    /**
     * Swaps in a gateway whose execute/status answers come from the given closures.
     */
    private function fakeGateway(?Closure $execute = null, ?Closure $status = null): object
    {
        $fake = new class($execute, $status) implements PaymentGateway
        {
            public int $executeCalls = 0;

            public function __construct(private ?Closure $onExecute, private ?Closure $onStatus) {}

            public function create(Order $order): array
            {
                return ['paymentID' => 'FAKE-1', 'bkashURL' => 'https://pay.example.test/FAKE-1'];
            }

            public function execute(string $paymentId): array
            {
                $this->executeCalls++;

                return $this->onExecute ? ($this->onExecute)($paymentId) : [];
            }

            public function status(string $paymentId): array
            {
                return $this->onStatus ? ($this->onStatus)($paymentId) : [];
            }
        };

        $this->app->instance(PaymentGateway::class, $fake);

        return $fake;
    }
}
