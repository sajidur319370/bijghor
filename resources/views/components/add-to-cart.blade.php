@props([
    'product',
    'quantity' => false,
])

@php($buttonClass = 'flex min-h-11 w-full items-center justify-center gap-2 rounded-lg px-2 text-sm font-semibold whitespace-nowrap sm:px-4 sm:text-base')

@if ($product->stock < 1)
    <button type="button" disabled class="{{ $buttonClass }} cursor-not-allowed bg-gray-100 text-gray-500">
        {{ __('Out of stock') }}
    </button>
@elseif (Route::has('cart.add'))
    <form method="POST" action="{{ route('cart.add') }}" x-data="{ busy: false }" @submit="busy = true" {{ $attributes->class('flex gap-2') }}>
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        @if ($quantity)
            <x-qty-stepper name="qty" :max="min($product->stock, 99)" />
        @else
            <input type="hidden" name="qty" value="1">
        @endif
        <button type="submit" :disabled="busy" class="{{ $buttonClass }} bg-brand-600 text-white hover:bg-brand-700 disabled:opacity-60">
            <x-heroicon-o-shopping-cart class="hidden size-5 sm:block" />
            {{ __('Add to cart') }}
        </button>
    </form>
@else
    {{-- Until the cart exists (Day 3) --}}
    <button type="button" disabled class="{{ $buttonClass }} cursor-not-allowed bg-brand-600/50 text-white">
        <x-heroicon-o-shopping-cart class="hidden size-5 sm:block" />
        {{ __('Add to cart') }}
    </button>
@endif
