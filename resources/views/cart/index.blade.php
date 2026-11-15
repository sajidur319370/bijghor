<x-layouts.app :title="__('Cart')" :noindex="true">
    <div class="mx-auto max-w-6xl px-4 py-6">
        <h1 class="text-2xl font-semibold text-soil-600">{{ __('Your cart') }}</h1>

        @foreach ($notices as $notice)
            <p role="status" class="mt-4 rounded-lg border-l-4 border-sun-400 bg-sun-400/15 px-4 py-3 text-gray-800">{{ $notice }}</p>
        @endforeach

        @if ($lines->isEmpty())
            <x-empty-state :title="__('Your cart is empty')" class="mt-6">
                <a href="{{ route('home') }}" class="font-semibold text-brand-700 hover:underline">{{ __('Browse all seeds') }}</a>
            </x-empty-state>
        @else
            <div class="mt-6 grid gap-6 lg:grid-cols-3 lg:items-start">
                <ul class="divide-y divide-gray-200 rounded-xl border border-gray-200 lg:col-span-2">
                    @foreach ($lines as $line)
                        @php($product = $line['product'])
                        <li class="flex gap-3 p-3 sm:gap-4 sm:p-4">
                            <a href="{{ route('product', $product->slug) }}" class="w-20 shrink-0 overflow-hidden rounded-lg sm:w-24">
                                <x-product-image :product="$product" />
                            </a>

                            <div class="flex min-w-0 flex-1 flex-col gap-2">
                                <div class="flex justify-between gap-3">
                                    <div class="min-w-0">
                                        <a href="{{ route('product', $product->slug) }}" class="font-semibold text-gray-900 hover:text-brand-700">{{ $product->name }}</a>
                                        <p class="text-sm text-gray-500">{{ $product->packet_size }} · {{ money($product->effective_price) }}</p>
                                    </div>
                                    <p class="shrink-0 font-semibold text-gray-900">{{ money($line['line_total']) }}</p>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <x-qty-stepper name="qty" :value="$line['quantity']" :max="min($product->stock, App\Services\Cart::MAX_QUANTITY)" :autosubmit="true" />
                                        <noscript>
                                            <button type="submit" class="min-h-11 rounded-lg border border-gray-300 px-3 text-sm font-semibold">{{ __('Update') }}</button>
                                        </noscript>
                                    </form>

                                    <form method="POST" action="{{ route('cart.update') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="qty" value="0">
                                        <button type="submit" class="flex min-h-11 items-center gap-1 px-2 text-sm text-gray-500 hover:text-red-700">
                                            <x-heroicon-o-trash class="size-5" />
                                            <span class="hidden sm:inline">{{ __('Remove') }}</span>
                                            <span class="sr-only sm:hidden">{{ __('Remove') }}</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <aside class="rounded-xl border border-gray-200 bg-brand-50 p-4">
                    <dl class="flex justify-between text-lg">
                        <dt class="font-semibold">{{ __('Subtotal') }}</dt>
                        <dd class="font-semibold">{{ money($subtotal) }}</dd>
                    </dl>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Delivery charge is added at checkout.') }}</p>
                    <a href="{{ route('checkout') }}" class="mt-4 flex min-h-11 items-center justify-center rounded-lg bg-brand-600 px-4 font-semibold text-white hover:bg-brand-700">
                        {{ __('Checkout') }}
                    </a>
                    <a href="{{ route('home') }}" class="mt-2 flex min-h-11 items-center justify-center text-sm font-semibold text-brand-700 hover:underline">
                        {{ __('Continue shopping') }}
                    </a>
                </aside>
            </div>
        @endif
    </div>
</x-layouts.app>
