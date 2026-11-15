<form action="{{ route('search') }}" method="GET" role="search" {{ $attributes->merge(['class' => 'relative']) }}>
    <label for="{{ $id = 'q-'.Str::random(4) }}" class="sr-only">{{ __('Search') }}</label>
    <input id="{{ $id }}" type="search" name="q" value="{{ request()->routeIs('search') ? request('q') : '' }}"
        placeholder="{{ __('Search seeds…') }}" maxlength="100"
        class="h-11 w-full rounded-full border border-gray-300 bg-gray-50 pr-12 pl-4 text-base placeholder:text-gray-500 focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-brand-600/20 focus:outline-none">
    <button type="submit" class="absolute top-0 right-0 flex size-11 items-center justify-center rounded-full text-gray-600 hover:text-brand-700">
        <x-heroicon-o-magnifying-glass class="size-5" />
        <span class="sr-only">{{ __('Search') }}</span>
    </button>
</form>
