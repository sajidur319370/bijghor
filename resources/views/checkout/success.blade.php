<x-layouts.app :title="__('Thank you for your order')" :noindex="true">
    <div class="mx-auto max-w-xl px-4 py-10 text-center">
        <span class="mx-auto flex size-16 items-center justify-center rounded-full bg-brand-100 text-brand-700">
            <x-heroicon-o-check class="size-9" />
        </span>
        <h1 class="mt-4 text-2xl font-semibold text-gray-900">{{ __('Thank you! Your order is confirmed.') }}</h1>
        <p class="mt-2 text-gray-600">{{ __('We have received your payment and will pack your seeds soon.') }}</p>

        <div class="mt-6 rounded-xl border-2 border-brand-600 bg-brand-50 px-4 py-4">
            <p class="text-sm text-gray-600">{{ __('Your order number') }}</p>
            <p class="mt-1 text-2xl font-semibold tracking-wider text-brand-800 select-all">{{ $order->order_number }}</p>
            <p class="mt-2 text-sm text-gray-700">{{ __('Save this number. You need it with your mobile number to track your order.') }}</p>
        </div>

        <x-order-summary :order="$order" class="mt-6" />

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
            <a href="{{ route('track') }}" class="flex min-h-11 items-center justify-center rounded-lg border border-brand-600 px-5 font-semibold text-brand-700 hover:bg-brand-50">
                {{ __('Track order') }}
            </a>
            <a href="{{ route('home') }}" class="flex min-h-11 items-center justify-center rounded-lg bg-brand-600 px-5 font-semibold text-white hover:bg-brand-700">
                {{ __('Continue shopping') }}
            </a>
        </div>
    </div>
</x-layouts.app>
