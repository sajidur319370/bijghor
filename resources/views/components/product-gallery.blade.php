@props([
    'product',
])

{{-- Main photo first, then the gallery photos in admin order. --}}
@php($photos = collect([$product->image_url])->merge($product->images->pluck('url'))->filter()->unique()->values())

@if ($photos->isEmpty())
    <x-product-image :product="$product" :lazy="false" class="rounded-xl" />
@else
    <div x-data="{ active: 0 }" {{ $attributes }}>
        <div class="overflow-hidden rounded-xl border border-gray-200">
            @foreach ($photos as $index => $photo)
                <x-product-image :product="$product" :src="$photo" :lazy="$index > 0"
                    x-show="active === {{ $index }}" :x-cloak="$index > 0 ? true : null" />
            @endforeach
        </div>

        @if ($photos->count() > 1)
            <div class="mt-3 grid grid-cols-5 gap-2">
                @foreach ($photos as $index => $photo)
                    <button type="button" @click="active = {{ $index }}"
                        :class="active === {{ $index }} ? 'ring-2 ring-brand-600' : 'opacity-70 hover:opacity-100'"
                        class="overflow-hidden rounded-lg border border-gray-200">
                        <img src="{{ $photo }}" alt="" width="120" height="120" loading="lazy" class="aspect-square w-full object-cover">
                        <span class="sr-only">{{ __('Show photo :number', ['number' => bn_digits($index + 1)]) }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>
@endif
