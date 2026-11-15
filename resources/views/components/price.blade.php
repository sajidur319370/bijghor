@props([
    'product',
    'size' => 'base',
])

@php($amountClass = $size === 'lg' ? 'text-2xl' : 'text-lg')

<p {{ $attributes->class('flex flex-wrap items-baseline gap-x-2') }}>
    @if ($product->is_on_sale)
        <span class="{{ $amountClass }} font-semibold text-brand-700">{{ money($product->sale_price) }}</span>
        <del class="text-sm text-gray-500"><span class="sr-only">{{ __('Regular price') }}:</span> {{ money($product->price) }}</del>
    @else
        <span class="{{ $amountClass }} font-semibold text-gray-900">{{ money($product->price) }}</span>
    @endif
</p>
