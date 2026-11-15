@props([
    'items' => [],
])

{{-- $items: [label => url|null]; the last item is the current page. --}}
<nav aria-label="{{ __('Breadcrumb') }}" {{ $attributes->class('text-sm text-gray-500') }}>
    <ol class="flex flex-wrap items-center gap-1">
        <li><a href="{{ route('home') }}" class="hover:text-brand-700">{{ __('Home') }}</a></li>
        @foreach ($items as $label => $url)
            <li aria-hidden="true">›</li>
            <li>
                @if ($url && ! $loop->last)
                    <a href="{{ $url }}" class="hover:text-brand-700">{{ $label }}</a>
                @else
                    <span aria-current="page" class="text-gray-700">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
