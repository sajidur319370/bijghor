<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Payment;
use App\Payments\PaymentGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Finds out from the gateway whether a payment really went through, and records it (docs §5.8).
 *
 * Used by the payment callback (execute, then status) and by the admin "Check with bKash"
 * action (status only). Rules:
 * - a completed payment is never processed twice;
 * - no clear answer (timeout, error) leaves the payment "initiated" so it can be checked again;
 * - the amount must match the order total, compared in paisa;
 * - money that arrives after the order was cancelled is recorded, but the order stays
 *   cancelled and shows up as "needs refund" (its stock may already be sold).
 */
class SettlePayment
{
    public function __construct(private PaymentGateway $gateway) {}

    /**
     * @return string the payment status afterwards: completed, failed or initiated (still unknown)
     */
    public function handle(Payment $payment, bool $execute = true): string
    {
        if ($payment->status === 'completed') {
            return 'completed';
        }

        $paymentId = $payment->bkash_payment_id;
        $result = ($execute ? $this->ask(fn () => $this->gateway->execute($paymentId)) : null)
            ?? $this->ask(fn () => $this->gateway->status($paymentId));

        if ($result === null) {
            Log::warning('Payment result unknown, left as initiated', ['payment_id' => $paymentId]);

            return 'initiated';
        }

        return DB::transaction(function () use ($payment, $result): string {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            if ($payment->status === 'completed') {
                return 'completed';   // another request finished first
            }

            $order = Order::whereKey($payment->order_id)->lockForUpdate()->first();

            $paid = $result['transactionStatus'] === 'Completed'
                && self::paisa($result['amount'] ?? 0) === self::paisa($order->total);

            $payment->update([
                'status' => $paid ? 'completed' : 'failed',
                'trx_id' => $paid ? ($result['trxID'] ?? null) : null,
                'payer_account' => $result['customerMsisdn'] ?? null,
                'raw_response' => $result,
                'completed_at' => $paid ? now() : null,
            ]);

            if (! $paid) {
                if ($order->payment_status === 'unpaid') {
                    $order->update(['payment_status' => 'failed']);
                }

                return 'failed';
            }

            if ($order->status === 'cancelled') {
                $order->update(['payment_status' => 'paid', 'paid_at' => now()]);
                Log::warning('Payment completed on a cancelled order: needs refund', ['order' => $order->order_number]);

                return 'completed';
            }

            $order->update(['payment_status' => 'paid', 'status' => 'confirmed', 'paid_at' => now()]);

            return 'completed';
        });
    }

    /**
     * Calls the gateway. Returns null when it threw or gave no transactionStatus.
     *
     * @param  callable(): array<string, mixed>  $call
     * @return array<string, mixed>|null
     */
    private function ask(callable $call): ?array
    {
        try {
            $result = $call();

            return isset($result['transactionStatus']) ? $result : null;
        } catch (Throwable $e) {
            Log::warning('Payment gateway call failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private static function paisa(string|int|float $taka): int
    {
        return (int) round((float) $taka * 100);
    }
}
