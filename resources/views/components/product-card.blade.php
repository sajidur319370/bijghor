@props([
    'product',
])

<article {{ $attributes->class('flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white transition hover:shadow-md') }}>
    <a href="{{ route('product', $product->slug) }}" class="group block">
        <div class="relative">
            <x-product-image :product="$product" class="transition group-hover:opacity-90" />

            <div class="absolute top-2 left-2 flex flex-col items-start gap-1">
                @if ($product->stock < 1)
                    <x-badge tone="stock">{{ __('Out of stock') }}</x-badge>
                @elseif ($product->is_on_sale)
                    <x-badge tone="sale">{{ __('Sale') }}</x-badge>
                @endif
            </div>
        </div>

        <div class="px-3 pt-3">
            <h3 class="line-clamp-2 font-semibold text-gray-900 group-hover:text-brand-700">{{ $product->name }}</h3>
            <p class="text-sm text-gray-500">{{ $product->packet_size }}</p>
            <x-price :product="$product" class="mt-1" />
        </div>
    </a>

    <div class="mt-auto p-3">
        <x-add-to-cart :product="$product" />
    </div>
</article>
