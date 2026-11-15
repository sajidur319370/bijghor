@props([
    'category',
])

@php($count = $category->products_count ?? 0)

<a href="{{ route('category', $category->slug) }}"
    {{ $attributes->class('group flex items-center gap-2.5 rounded-xl border border-gray-200 bg-white p-2.5 transition hover:border-brand-600 hover:shadow-sm sm:gap-3 sm:p-3') }}>
    @if ($category->image)
        <img src="{{ Storage::disk('uploads')->url($category->image) }}" alt="" width="56" height="56" loading="lazy"
            class="size-11 shrink-0 rounded-lg object-cover sm:size-14">
    @else
        <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 sm:size-14">
            <x-icons.sprout class="size-6 sm:size-8" />
        </span>
    @endif

    <span class="min-w-0">
        <span class="block font-semibold text-soil-600 group-hover:text-brand-700">{{ $category->name }}</span>
        <span class="block text-sm text-gray-500">{{ trans_choice(':count product|:count products', $count, ['count' => bn_digits($count)]) }}</span>
    </span>
</a>
