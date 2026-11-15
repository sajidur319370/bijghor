@props([
    'products',
])

{{-- 2 columns on phones, 3 on tablets, 4 on desktop (docs §2.5) --}}
<div {{ $attributes->class('grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4') }}>
    @foreach ($products as $product)
        <x-product-card :product="$product" />
    @endforeach
</div>
