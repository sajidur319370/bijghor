{{-- $navCategories and $cartCount come from the view composer in AppServiceProvider. --}}
@php($cartTag = Route::has('cart') ? 'a' : 'span')

<header x-data="{ open: false }" class="sticky top-0 z-30 border-b border-gray-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center gap-2 px-4 py-2 sm:gap-4">
        <button type="button" @click="open = ! open" :aria-expanded="open" aria-expanded="false" aria-controls="mobile-menu"
            class="-ml-2.5 flex size-11 items-center justify-center rounded-full text-gray-700 hover:bg-gray-100 md:hidden">
            <x-heroicon-o-bars-3 class="size-6" x-show="! open" />
            <x-heroicon-o-x-mark class="size-6" x-show="open" x-cloak />
            <span class="sr-only">{{ __('Menu') }}</span>
        </button>

        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-1.5 text-xl font-semibold text-brand-700">
            <x-icons.sprout class="size-7" />
            <span>{{ __('BijGhor') }}</span>
        </a>

        <x-search-form class="mx-4 hidden max-w-md flex-1 md:block" />

        <div class="ml-auto flex items-center gap-1 sm:gap-2">
            <x-lang-switch />

            {{-- A link once the cart page exists (Day 3) --}}
            <{{ $cartTag }} @if (Route::has('cart')) href="{{ route('cart') }}" @endif
                class="relative flex size-11 items-center justify-center rounded-full text-gray-700 hover:bg-gray-100">
                <x-heroicon-o-shopping-cart class="size-6" />
                <span class="sr-only">{{ __('Cart') }}</span>
                @if ($cartCount > 0)
                    <span class="absolute top-0.5 right-0.5 flex min-w-5 items-center justify-center rounded-full bg-brand-600 px-1 text-xs font-semibold text-white">{{ bn_digits($cartCount) }}</span>
                @endif
            </{{ $cartTag }}>
        </div>
    </div>

    <div class="px-4 pb-3 md:hidden">
        <x-search-form />
    </div>

    @if ($navCategories->isNotEmpty())
        <nav aria-label="{{ __('Categories') }}" class="hidden border-t border-gray-100 md:block">
            <ul class="mx-auto flex max-w-6xl gap-6 px-4 text-sm">
                @foreach ($navCategories as $navCategory)
                    <li>
                        <a href="{{ route('category', $navCategory->slug) }}" @class([
                            'block py-2.5 hover:text-brand-700',
                            'font-semibold text-brand-700' => request()->routeIs('category') && request()->route('slug') === $navCategory->slug,
                            'text-gray-700' => ! (request()->routeIs('category') && request()->route('slug') === $navCategory->slug),
                        ])>{{ $navCategory->name }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <nav id="mobile-menu" x-show="open" x-cloak @keydown.escape.window="open = false" aria-label="{{ __('Categories') }}"
            class="border-t border-gray-100 md:hidden">
            <ul class="px-2 py-2">
                @foreach ($navCategories as $navCategory)
                    <li>
                        <a href="{{ route('category', $navCategory->slug) }}"
                            class="flex min-h-11 items-center rounded-lg px-3 text-base text-gray-800 hover:bg-brand-50">{{ $navCategory->name }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif
</header>
