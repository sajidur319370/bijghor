{{-- Site-wide pagination (set in AppServiceProvider). Big tap targets, Bangla digits, no dark-mode styles. --}}
@if ($paginator->hasPages())
    @php($linkClass = 'flex min-h-11 items-center gap-1 rounded-lg border px-4 font-semibold')

    <nav role="navigation" aria-label="{{ __('Pagination') }}" class="flex items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="{{ $linkClass }} border-gray-200 text-gray-400">
                <x-heroicon-o-chevron-left class="size-4" /> {{ __('Previous') }}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $linkClass }} border-gray-300 text-gray-700 hover:border-brand-600 hover:text-brand-700">
                <x-heroicon-o-chevron-left class="size-4" /> {{ __('Previous') }}
            </a>
        @endif

        <p class="text-sm text-gray-600">
            {{ __('Page :current of :last', ['current' => bn_digits($paginator->currentPage()), 'last' => bn_digits($paginator->lastPage())]) }}
        </p>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $linkClass }} border-gray-300 text-gray-700 hover:border-brand-600 hover:text-brand-700">
                {{ __('Next') }} <x-heroicon-o-chevron-right class="size-4" />
            </a>
        @else
            <span aria-disabled="true" class="{{ $linkClass }} border-gray-200 text-gray-400">
                {{ __('Next') }} <x-heroicon-o-chevron-right class="size-4" />
            </span>
        @endif
    </nav>
@endif
