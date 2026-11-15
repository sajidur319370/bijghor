<?php

namespace App\Http\Controllers;

use App\Actions\SettlePayment;
use App\Models\Order;
use App\Models\Payment;
use App\Payments\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Starts a payment attempt and sends the customer to bKash (or the demo page).
     * Each visit is a new attempt, so "Try again" after a failure works.
     */
    public function pay(string $number, PaymentGateway $gateway): RedirectResponse
    {
        $order = Order::where('order_number', $number)
            ->where('status', 'pending')
            ->where('payment_status', '!=', 'paid')
            ->firstOrFail();

        try {
            $response = $gateway->create($order);
        } catch (Throwable $e) {          // timeout / connection error throws, it doesn't return
            Log::warning('Payment create failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);
            $response = [];
        }

        if (! isset($response['paymentID'], $response['bkashURL'])) {
            return redirect()->route('checkout.failed', $order->order_number)
                ->with('error', __('bKash is unavailable, please try again'));
        }

        $order->payments()->create([
            'method' => 'bkash',
            'bkash_payment_id' => $response['paymentID'],
            'amount' => $order->total,
            'status' => 'initiated',
            'raw_response' => $response,
        ]);

        return redirect()->away($response['bkashURL']);
    }

    /**
     * bKash (or the demo page) sends the customer back here with ?paymentID=…&status=success|failure|cancel.
     * Anyone can type this URL, so only the gateway's own answer can mark a payment as paid.
     */
    public function callback(Request $request, SettlePayment $settle): RedirectResponse
    {
        $payment = Payment::with('order')
            ->where('bkash_payment_id', (string) $request->query('paymentID'))
            ->firstOrFail();
        $order = $payment->order;

        // 1. Already done (refresh / double callback): don't call bKash again
        if ($payment->status === 'completed') {
            return $this->result($order);
        }

        // 2. Failure / cancel: only touches an attempt that is still open
        if ($request->query('status') !== 'success') {
            Payment::whereKey($payment->id)->where('status', 'initiated')
                ->update(['status' => $request->query('status') === 'cancel' ? 'cancelled' : 'failed']);
            Order::whereKey($order->id)->where('payment_status', 'unpaid')
                ->update(['payment_status' => 'failed']);

            return redirect()->route('checkout.failed', $order->order_number);
        }

        // 3. Ask the gateway what really happened
        if ($settle->handle($payment) === 'initiated') {
            return redirect()->route('checkout.failed', $order->order_number)
                ->with('error', __('We could not confirm your payment yet. If money was taken, we will confirm your order. Please do not pay twice.'));
        }

        return $this->result($order->fresh());
    }

    private function result(Order $order): RedirectResponse
    {
        if ($order->payment_status !== 'paid') {
            return redirect()->route('checkout.failed', $order->order_number);
        }

        if ($order->status === 'cancelled') {
            return redirect()->route('checkout.failed', $order->order_number)
                ->with('error', __('This order was cancelled before your payment finished. Your payment will be refunded.'));
        }

        return redirect()->route('checkout.success', $order->order_number);
    }
}
