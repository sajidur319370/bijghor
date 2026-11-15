<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Models\Order;
use App\Models\User;
use App\Payments\PaymentGateway;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class PaymentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_payments_are_listed_with_their_order_and_transaction_id(): void
    {
        $order = Order::factory()->paid()->create();
        $payment = $order->payments()->create(['bkash_payment_id' => 'TR001', 'trx_id' => 'BFD90JRLST', 'amount' => $order->total, 'status' => 'completed']);

        Livewire::test(ListPayments::class)
            ->assertCanSeeTableRecords([$payment])
            ->assertSee('BFD90JRLST')
            ->assertSee($order->order_number);
    }

    public function test_payments_cannot_be_created_or_edited_by_hand(): void
    {
        $this->get('/admin/payments/create')->assertNotFound();
    }

    public function test_check_with_bkash_is_only_offered_for_open_attempts(): void
    {
        $order = Order::factory()->create();
        $open = $order->payments()->create(['bkash_payment_id' => 'TR-OPEN', 'amount' => $order->total]);
        $done = $order->payments()->create(['bkash_payment_id' => 'TR-DONE', 'amount' => $order->total, 'status' => 'failed']);

        Livewire::test(ListPayments::class)
            ->assertActionVisible(TestAction::make('checkWithBkash')->table($open))
            ->assertActionHidden(TestAction::make('checkWithBkash')->table($done));
    }

    public function test_check_with_bkash_confirms_a_payment_that_actually_went_through(): void
    {
        $order = Order::factory()->create();
        $payment = $order->payments()->create(['bkash_payment_id' => 'TR001', 'amount' => $order->total]);
        $gateway = $this->fakeGateway(fn () => [
            'paymentID' => 'TR001', 'trxID' => 'LATE123', 'transactionStatus' => 'Completed', 'amount' => '458.00',
        ]);

        Livewire::test(ListPayments::class)
            ->callAction(TestAction::make('checkWithBkash')->table($payment))
            ->assertNotified('Paid: the payment is now completed');

        $this->assertSame(0, $gateway->executeCalls);   // only /payment/status is asked
        $this->assertSame('completed', $payment->fresh()->status);
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_check_with_bkash_keeps_the_attempt_open_when_bkash_is_unreachable(): void
    {
        $order = Order::factory()->create();
        $payment = $order->payments()->create(['bkash_payment_id' => 'TR001', 'amount' => $order->total]);
        $this->fakeGateway(fn () => throw new RuntimeException('timed out'));

        Livewire::test(ListPayments::class)
            ->callAction(TestAction::make('checkWithBkash')->table($payment))
            ->assertNotified('bKash gave no clear answer. Try again later.');

        $this->assertSame('initiated', $payment->fresh()->status);
    }

    private function fakeGateway(callable $status): object
    {
        $fake = new class($status) implements PaymentGateway
        {
            public int $executeCalls = 0;

            /** @var callable */
            private $onStatus;

            public function __construct(callable $onStatus)
            {
                $this->onStatus = $onStatus;
            }

            public function create(Order $order): array
            {
                return [];
            }

            public function execute(string $paymentId): array
            {
                $this->executeCalls++;

                return [];
            }

            public function status(string $paymentId): array
            {
                return ($this->onStatus)($paymentId);
            }
        };

        $this->app->instance(PaymentGateway::class, $fake);

        return $fake;
    }
}
