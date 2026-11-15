@props([
    'product',
    'src' => null,
    'lazy' => true,
])

@php($src ??= $product->image_url)

@if ($src)
    <img src="{{ $src }}" alt="{{ $product->name }}" width="800" height="800" decoding="async"
        @if ($lazy) loading="lazy" @endif
        {{ $attributes->class('aspect-square w-full object-cover') }}>
@else
    {{-- No photo yet: a seedling placeholder in the same square --}}
    <div role="img" aria-label="{{ $product->name }}" {{ $attributes->class('flex aspect-square w-full items-center justify-center bg-brand-50 text-brand-600/40') }}>
        <x-icons.sprout class="size-1/3" />
    </div>
@endif
