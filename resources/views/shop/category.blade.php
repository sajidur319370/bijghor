<x-layouts.app :title="$category->name"
    :description="__('Buy :category online: :count seeds, each with sowing season and germination rate.', ['category' => $category->name, 'count' => bn_digits($products->total())])"
    :image="$category->image ? Storage::disk('uploads')->url($category->image) : null">
    <div class="mx-auto max-w-6xl px-4 py-6">
        <x-breadcrumbs :items="[$category->name => null]" />

        <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-soil-600">{{ $category->name }}</h1>
                <p class="text-sm text-gray-500">
                    {{ trans_choice(':count product|:count products', $products->total(), ['count' => bn_digits($products->total())]) }}
                </p>
            </div>

            @if ($products->total() > 1)
                <form method="GET" action="{{ route('category', $category->slug) }}" class="flex items-center gap-2">
                    <label for="sort" class="text-sm text-gray-600">{{ __('Sort by') }}</label>
                    <select id="sort" name="sort" x-data @change="$el.form.submit()"
                        class="h-11 rounded-lg border border-gray-300 bg-white px-3 text-base focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 focus:outline-none">
                        @foreach (['newest' => __('Newest'), 'price_asc' => __('Price: low to high'), 'price_desc' => __('Price: high to low')] as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <noscript>
                        <button type="submit" class="h-11 rounded-lg bg-brand-600 px-4 font-semibold text-white">{{ __('Sort') }}</button>
                    </noscript>
                </form>
            @endif
        </div>

        @if ($products->isNotEmpty())
            <x-product-grid :products="$products" class="mt-6" />
            <div class="mt-8">{{ $products->links() }}</div>
        @else
            <x-empty-state :title="__('No seeds in this category yet')" class="mt-6">
                <a href="{{ route('home') }}" class="font-semibold text-brand-700 hover:underline">{{ __('Browse all seeds') }}</a>
            </x-empty-state>
        @endif
    </div>
</x-layouts.app>
