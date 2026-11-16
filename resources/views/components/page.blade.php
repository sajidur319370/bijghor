@props([
    'title',
    'updated' => false,
])

{{-- Long-form text page (About, policies). Styles plain h2 / p / ul / a inside the slot. --}}
<x-layouts.app :title="$title" {{ $attributes }}>
    <article class="mx-auto max-w-3xl px-4 py-8
        [&_a]:font-semibold [&_a]:text-brand-700 [&_a]:underline
        [&_h2]:mt-8 [&_h2]:text-lg [&_h2]:font-semibold [&_h2]:text-soil-600
        [&_li]:mt-1 [&_p]:mt-3 [&_p]:text-gray-700
        [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:text-gray-700">
        <h1 class="text-2xl font-semibold text-soil-600 md:text-3xl">{{ $title }}</h1>

        @if ($updated)
            <p class="text-sm text-gray-500!">
                {{ __('Last updated: :date', ['date' => bn_digits(\Illuminate\Support\Carbon::parse(config('shop.policies_updated_at'))->format('d/m/Y'))]) }}
            </p>
        @endif

        <div class="mt-4 rounded-lg border-l-4 border-sun-400 bg-sun-400/15 px-4 py-3 text-gray-800 [&_p]:mt-0">
            <p>{{ __('This is a demo shop. Payments run in bKash test (sandbox) or demo mode, so no real money is charged.') }}</p>
        </div>

        {{ $slot }}
    </article>
</x-layouts.app>
