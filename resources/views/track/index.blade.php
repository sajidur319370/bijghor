@php
    $inputClass = 'block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-base focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 focus:outline-none';
    $steps = ['pending' => __('Order placed'), 'confirmed' => __('Confirmed'), 'shipped' => __('Shipped'), 'delivered' => __('Delivered')];
    $paymentLabels = ['unpaid' => __('Unpaid'), 'paid' => __('Paid'), 'failed' => __('Payment failed'), 'refunded' => __('Refunded')];
@endphp

<x-layouts.app :title="__('Track order')" :noindex="true">
    <div class="mx-auto max-w-xl px-4 py-8">
        <h1 class="text-2xl font-semibold text-soil-600">{{ __('Track order') }}</h1>
        <p class="mt-1 text-gray-600">{{ __('Enter your order number and the mobile number you ordered with.') }}</p>

        <form method="POST" action="{{ route('track') }}" class="mt-5 space-y-4 rounded-xl border border-gray-200 p-4">
            @csrf
            <x-field name="order_number" :label="__('Order number')">
                <input id="order_number" name="order_number" type="text" value="{{ old('order_number', request('order_number')) }}" required maxlength="30"
                    placeholder="BG-261115-7K3QD" autocapitalize="characters" class="{{ $inputClass }} uppercase">
            </x-field>
            <x-field name="phone" :label="__('Mobile number')">
                <input id="phone" name="phone" type="tel" value="{{ old('phone', request('phone')) }}" required maxlength="20" inputmode="tel"
                    placeholder="01XXXXXXXXX" class="{{ $inputClass }}">
            </x-field>
            <button type="submit" class="flex min-h-11 w-full items-center justify-center rounded-lg bg-brand-600 px-4 font-semibold text-white hover:bg-brand-700">
                {{ __('Track') }}
            </button>
        </form>

        @if ($searched && ! $order)
            <x-empty-state :title="__('Order not found')" class="mt-6">
                {{ __('Check the order number and use the same mobile number you ordered with.') }}
            </x-empty-state>
        @endif

        @if ($order)
            <section class="mt-6 rounded-xl border border-gray-200 p-4" aria-labelledby="order-heading">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h2 id="order-heading" class="text-lg font-semibold">{{ $order->order_number }}</h2>
                    <p class="text-sm text-gray-500">{{ __('Placed on :date', ['date' => bn_digits($order->created_at->format('d/m/Y'))]) }}</p>
                </div>

                @if ($order->status === 'cancelled')
                    <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 font-semibold text-red-800">{{ __('This order was cancelled.') }}</p>
                @else
                    @php($reached = array_search($order->status, array_keys($steps), true))
                    <ol class="mt-5 grid grid-cols-4 gap-1 text-center text-xs sm:text-sm">
                        @foreach ($steps as $status => $label)
                            @php($done = $loop->index <= $reached)
                            <li @if ($status === $order->status) aria-current="step" @endif>
                                <span @class(['mx-auto flex size-8 items-center justify-center rounded-full', 'bg-brand-600 text-white' => $done, 'bg-gray-200 text-gray-500' => ! $done])>
                                    @if ($done)
                                        <x-heroicon-o-check class="size-5" />
                                    @else
                                        {{ bn_digits($loop->iteration) }}
                                    @endif
                                </span>
                                <span @class(['mt-1 block', 'font-semibold text-brand-800' => $done, 'text-gray-500' => ! $done])>{{ $label }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif

                <p class="mt-4 text-sm">
                    {{ __('Payment') }}:
                    <x-badge :tone="$order->payment_status === 'paid' ? 'success' : 'neutral'">{{ $paymentLabels[$order->payment_status] }}</x-badge>
                </p>

                <x-order-summary :order="$order" class="mt-4" />

                @if ($order->status === 'pending' && $order->payment_status !== 'paid')
                    <a href="{{ route('order.pay', $order->order_number) }}"
                        class="mt-4 flex min-h-11 items-center justify-center rounded-lg bg-brand-600 px-4 font-semibold text-white hover:bg-brand-700">
                        {{ __('Pay now') }}
                    </a>
                @endif
            </section>
        @endif
    </div>
</x-layouts.app>
