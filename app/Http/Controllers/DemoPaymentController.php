<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\View\View;

class DemoPaymentController extends Controller
{
    /**
     * The demo payment page: Pay / Fail / Cancel send the visitor to the normal callback.
     * Only exists while PAYMENT_DRIVER=demo, and only for attempts that are still open.
     */
    public function __invoke(string $paymentId): View
    {
        abort_unless(config('services.payment.driver') === 'demo', 404);

        $payment = Payment::with('order')
            ->where('bkash_payment_id', $paymentId)
            ->where('status', 'initiated')
            ->firstOrFail();

        return view('demo-pay', ['payment' => $payment]);
    }
}
