<x-layouts.app :title="$term !== '' ? __('Search: :term', ['term' => $term]) : __('All seeds')">
    <div class="mx-auto max-w-6xl px-4 py-6">
        <x-breadcrumbs :items="[__('Search') => null]" />

        <h1 class="mt-3 text-2xl font-semibold text-soil-600">
            @if ($term !== '')
                {{ __('Results for “:term”', ['term' => $term]) }}
            @else
                {{ __('All seeds') }}
            @endif
        </h1>
        <p class="text-sm text-gray-500">
            {{ trans_choice(':count product|:count products', $products->total(), ['count' => bn_digits($products->total())]) }}
        </p>

        @if ($products->isNotEmpty())
            <x-product-grid :products="$products" class="mt-6" />
            <div class="mt-8">{{ $products->links() }}</div>
        @else
            <x-empty-state :title="__('No seeds found')" class="mt-6">
                {{ __('Try another name, in Bangla or English.') }}
                <a href="{{ route('home') }}" class="font-semibold text-brand-700 hover:underline">{{ __('Browse all seeds') }}</a>
            </x-empty-state>
        @endif
    </div>
</x-layouts.app>
