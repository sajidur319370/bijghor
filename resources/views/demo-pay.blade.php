{{-- Deliberately plain: it must not look like bKash (no logo, colours or layout copied). --}}
<x-layouts.app :title="__('Demo payment')" :noindex="true">
    <div class="mx-auto max-w-md px-4 py-10">
        <div class="rounded-2xl border-2 border-dashed border-gray-400 bg-white p-6 text-center">
            <p class="inline-block rounded bg-gray-900 px-3 py-1 text-sm font-semibold tracking-wide text-white">{{ __('DEMO PAYMENT') }}</p>
            <p class="mt-3 text-gray-700">{{ __('No real money is charged. Choose what should happen:') }}</p>

            <dl class="mt-5 space-y-1 text-left">
                <div class="flex justify-between">
                    <dt class="text-gray-500">{{ __('Order number') }}</dt>
                    <dd class="font-semibold">{{ $payment->order->order_number }}</dd>
                </div>
                <div class="flex justify-between text-lg">
                    <dt class="text-gray-500">{{ __('Amount') }}</dt>
                    <dd class="font-semibold">{{ money($payment->amount) }}</dd>
                </div>
            </dl>

            <div class="mt-6 grid gap-2">
                <a href="{{ route('bkash.callback', ['paymentID' => $payment->bkash_payment_id, 'status' => 'success']) }}"
                    class="flex min-h-11 items-center justify-center rounded-lg bg-gray-900 px-4 font-semibold text-white hover:bg-gray-700">{{ __('Pay (demo)') }}</a>
                <a href="{{ route('bkash.callback', ['paymentID' => $payment->bkash_payment_id, 'status' => 'failure']) }}"
                    class="flex min-h-11 items-center justify-center rounded-lg border border-gray-400 px-4 font-semibold text-gray-800 hover:bg-gray-100">{{ __('Fail') }}</a>
                <a href="{{ route('bkash.callback', ['paymentID' => $payment->bkash_payment_id, 'status' => 'cancel']) }}"
                    class="flex min-h-11 items-center justify-center rounded-lg px-4 font-semibold text-gray-600 hover:bg-gray-100">{{ __('Cancel') }}</a>
            </div>
        </div>
    </div>
</x-layouts.app>
