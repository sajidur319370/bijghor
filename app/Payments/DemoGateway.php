<?php

namespace App\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

/**
 * Demo driver: a plain "Demo payment" page on this site with Pay / Fail / Cancel buttons.
 * No money and no data leave the server. Used when PAYMENT_DRIVER=demo.
 */
class DemoGateway implements PaymentGateway
{
    public function create(Order $order): array
    {
        $paymentId = 'DEMO-'.Str::upper(Str::random(12));

        return [
            'paymentID' => $paymentId,
            'bkashURL' => route('demo-pay.show', $paymentId),
            'statusCode' => '0000',
            'statusMessage' => 'Successful',
        ];
    }

    /** The visitor pressed "Pay" on the demo page, so the payment completes for its full amount. */
    public function execute(string $paymentId): array
    {
        $payment = Payment::where('bkash_payment_id', $paymentId)->firstOrFail();

        return [
            'paymentID' => $paymentId,
            'trxID' => 'DEMO'.Str::upper(Str::random(8)),
            'transactionStatus' => 'Completed',
            'amount' => (string) $payment->amount,
            'customerMsisdn' => '01XXXXXXXXX',
            'statusCode' => '0000',
        ];
    }

    public function status(string $paymentId): array
    {
        return $this->execute($paymentId);
    }
}
