{{-- Each language is written in its own script, so it is never translated. --}}
<nav aria-label="{{ __('Language') }}" {{ $attributes->merge(['class' => 'flex items-center rounded-full border border-gray-300 text-sm']) }}>
    @foreach (['en' => 'EN', 'bn' => 'বাংলা'] as $locale => $label)
        @if (app()->getLocale() === $locale)
            <span aria-current="true" class="flex h-9 items-center rounded-full bg-brand-600 px-3 font-semibold text-white">{{ $label }}</span>
        @else
            <a href="{{ route('lang', $locale) }}" lang="{{ $locale }}" hreflang="{{ $locale }}"
                class="flex h-9 items-center rounded-full px-3 text-gray-700 hover:text-brand-700">{{ $label }}</a>
        @endif
    @endforeach
</nav>
