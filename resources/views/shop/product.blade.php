<x-layouts.app :title="$product->name.' — '.$product->category->name" type="product" :image="$product->image_url"
    :description="$product->description ?: __(':name seeds, :size packet. Sowing season: :season.', ['name' => $product->name, 'size' => $product->packet_size, 'season' => $product->sowing_season])">
    <x-slot:head>
        <meta property="product:price:amount" content="{{ $product->effective_price }}">
        <meta property="product:price:currency" content="BDT">
        <meta property="product:availability" content="{{ $product->stock > 0 ? 'in stock' : 'out of stock' }}">
    </x-slot:head>
    <div class="mx-auto max-w-6xl px-4 py-6">
        <x-breadcrumbs :items="[
            $product->category->name => route('category', $product->category->slug),
            $product->name => null,
        ]" />

        <div class="mt-4 grid gap-6 md:grid-cols-2 md:gap-10">
            <div class="relative">
                <x-product-gallery :product="$product" />

                <div class="absolute top-3 left-3">
                    @if ($product->stock < 1)
                        <x-badge tone="stock">{{ __('Out of stock') }}</x-badge>
                    @elseif ($product->is_on_sale)
                        <x-badge tone="sale">{{ __('Sale') }}</x-badge>
                    @endif
                </div>
            </div>

            <div>
                <h1 class="text-2xl leading-snug font-semibold text-gray-900 md:text-3xl">{{ $product->name }}</h1>
                <p class="mt-1 text-gray-500">{{ $product->packet_size }}</p>

                <x-price :product="$product" size="lg" class="mt-3" />

                <p class="mt-2 text-sm">
                    @if ($product->stock < 1)
                        <span class="font-semibold text-red-700">{{ __('Out of stock') }}</span>
                    @elseif ($product->stock <= 10)
                        <span class="font-semibold text-soil-600">{{ __('Only :count left', ['count' => bn_digits($product->stock)]) }}</span>
                    @else
                        <span class="font-semibold text-brand-700">{{ __('In stock') }}</span>
                    @endif
                </p>

                <div class="mt-5">
                    <x-add-to-cart :product="$product" :quantity="true" />
                </div>

                <dl class="mt-6 divide-y divide-gray-100 rounded-xl border border-gray-200 text-sm">
                    @foreach ([
                        __('Packet size') => $product->packet_size,
                        __('Sowing season') => $product->sowing_season,
                        __('Germination rate') => $product->germination_rate !== null ? bn_digits($product->germination_rate).'%' : null,
                        __('Days to harvest') => $product->days_to_harvest !== null ? __(':days days', ['days' => bn_digits($product->days_to_harvest)]) : null,
                    ] as $label => $value)
                        @if ($value)
                            <div class="flex justify-between gap-4 px-4 py-3">
                                <dt class="text-gray-500">{{ $label }}</dt>
                                <dd class="text-right font-semibold text-gray-900">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                    <div class="flex justify-between gap-4 px-4 py-3">
                        <dt class="text-gray-500">{{ __('Category') }}</dt>
                        <dd class="text-right">
                            <a href="{{ route('category', $product->category->slug) }}" class="font-semibold text-brand-700 hover:underline">{{ $product->category->name }}</a>
                        </dd>
                    </div>
                </dl>

                @if ($product->description)
                    <div class="mt-6">
                        <h2 class="font-semibold text-soil-600">{{ __('About this seed') }}</h2>
                        {{-- Plain text for now; if Day 4 stores rich-editor HTML, it must be sanitized before output. --}}
                        <p class="mt-2 whitespace-pre-line text-gray-700">{{ $product->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-12" aria-labelledby="related-heading">
                <h2 id="related-heading" class="text-xl font-semibold text-soil-600">{{ __('You may also like') }}</h2>
                <x-product-grid :products="$related" class="mt-4" />
            </section>
        @endif
    </div>
</x-layouts.app>
