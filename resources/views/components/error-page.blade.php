@props([
    'code',
    'bn',
    'en',
    'bnHint',
    'enHint',
])

{{-- Error pages show both languages: on unknown URLs the session (and so the chosen language) isn't loaded. --}}
<x-layouts.app :title="$en" :noindex="true">
    <div class="mx-auto max-w-xl px-4 py-16 text-center">
        <x-icons.sprout class="mx-auto size-16 text-brand-600/40" />
        <p class="mt-4 text-5xl font-semibold text-soil-600">{{ bn_digits($code) }}</p>

        <h1 class="mt-4 text-2xl font-semibold text-gray-900" lang="bn">{{ $bn }}</h1>
        <p class="mt-1 text-gray-600" lang="bn">{{ $bnHint }}</p>

        <p class="mt-5 text-lg font-semibold text-gray-800" lang="en">{{ $en }}</p>
        <p class="mt-1 text-gray-600" lang="en">{{ $enHint }}</p>

        <div class="mt-8 flex flex-col gap-2 sm:flex-row sm:justify-center">
            <a href="{{ route('home') }}" class="flex min-h-11 items-center justify-center rounded-lg bg-brand-600 px-5 font-semibold text-white hover:bg-brand-700">
                হোম · Home
            </a>
            <a href="{{ route('search') }}" class="flex min-h-11 items-center justify-center rounded-lg border border-gray-300 px-5 font-semibold text-gray-700 hover:bg-gray-50">
                সব বীজ · All seeds
            </a>
        </div>
    </div>
</x-layouts.app>
