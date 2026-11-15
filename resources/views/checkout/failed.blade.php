<x-layouts.app :title="__('Payment not completed')" :noindex="true">
    <div class="mx-auto max-w-xl px-4 py-10 text-center">
        <span class="mx-auto flex size-16 items-center justify-center rounded-full bg-red-50 text-red-700">
            <x-heroicon-o-exclamation-triangle class="size-9" />
        </span>
        <h1 class="mt-4 text-2xl font-semibold text-gray-900">{{ __('Payment not completed') }}</h1>

        @if ($order->status === 'pending')
            <p class="mt-2 text-gray-600">{{ __('Your order is saved. You can try paying again.') }}</p>
        @else
            <p class="mt-2 text-gray-600">{{ __('This order is no longer open for payment.') }}</p>
        @endif

        <p class="mt-4 text-sm text-gray-500">{{ __('Order number') }}: <span class="font-semibold text-gray-800">{{ $order->order_number }}</span></p>

        <x-order-summary :order="$order" class="mt-6" />

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
            @if ($order->status === 'pending')
                <a href="{{ route('order.pay', $order->order_number) }}"
                    class="flex min-h-11 items-center justify-center rounded-lg bg-brand-600 px-5 font-semibold text-white hover:bg-brand-700">
                    {{ __('Try again') }}
                </a>
            @endif
            <a href="{{ route('home') }}" class="flex min-h-11 items-center justify-center rounded-lg border border-gray-300 px-5 font-semibold text-gray-700 hover:bg-gray-50">
                {{ __('Back to shop') }}
            </a>
        </div>
    </div>
</x-layouts.app>
