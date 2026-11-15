@props([
    'title',
])

<div {{ $attributes->class('flex flex-col items-center rounded-xl border border-dashed border-gray-300 px-6 py-12 text-center') }}>
    <x-icons.sprout class="size-12 text-brand-600/40" />
    <p class="mt-3 font-semibold text-gray-800">{{ $title }}</p>
    @if ($slot->isNotEmpty())
        <div class="mt-1 text-gray-600">{{ $slot }}</div>
    @endif
</div>
