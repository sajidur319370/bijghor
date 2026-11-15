<x-layouts.app>
    <section class="bg-brand-50">
        <div class="mx-auto grid max-w-6xl items-center gap-6 px-4 py-10 md:grid-cols-2 md:py-16">
            <div>
                <p class="font-semibold text-soil-600">{{ __('Seeds for every garden') }}</p>
                <h1 class="mt-2 text-3xl leading-snug font-semibold text-gray-900 md:text-4xl">
                    {{ __('Grow your own vegetables, flowers and herbs') }}
                </h1>
                <p class="mt-3 max-w-prose text-gray-600">
                    {{ __('Every packet shows the sowing season, germination rate and days to harvest. Order online, no account needed.') }}
                </p>
                <a href="#featured" class="mt-6 inline-flex min-h-11 items-center rounded-lg bg-brand-600 px-6 font-semibold text-white hover:bg-brand-700">
                    {{ __('Shop seeds') }}
                </a>
            </div>
            <div class="hidden justify-center md:flex">
                <x-icons.sprout class="size-56 text-brand-600/30" />
            </div>
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pt-10" aria-labelledby="categories-heading">
            <h2 id="categories-heading" class="text-xl font-semibold text-soil-600">{{ __('Categories') }}</h2>
            <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ($categories as $category)
                    <x-category-tile :category="$category" />
                @endforeach
            </div>
        </section>
    @endif

    <section id="featured" class="mx-auto max-w-6xl scroll-mt-32 px-4 pt-10" aria-labelledby="featured-heading">
        <h2 id="featured-heading" class="text-xl font-semibold text-soil-600">{{ __('Featured seeds') }}</h2>

        @if ($featured->isNotEmpty())
            <x-product-grid :products="$featured" class="mt-4" />
        @else
            <x-empty-state :title="__('New seeds are coming soon')" class="mt-4" />
        @endif
    </section>
</x-layouts.app>
