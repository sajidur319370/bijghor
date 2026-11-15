{{-- Links appear by themselves once their route exists (track: Day 3, policy pages: Day 5). --}}
@php($footerLinks = [
    'track' => 'Track order',
    'about' => 'About',
    'contact' => 'Contact',
    'terms' => 'Terms',
    'privacy' => 'Privacy',
    'refund-policy' => 'Refund policy',
])

<footer class="mt-16 border-t border-gray-200 bg-brand-50">
    <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 text-sm text-gray-600 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <x-icons.sprout class="size-6 text-brand-700" />
            <span>{{ __('BijGhor') }} · {{ __('Seeds for every garden') }}</span>
        </div>

        <ul class="flex flex-wrap gap-x-5 gap-y-1">
            @foreach ($footerLinks as $routeName => $label)
                @if (Route::has($routeName))
                    <li><a href="{{ route($routeName) }}" class="inline-block py-1 hover:text-brand-700">{{ __($label) }}</a></li>
                @endif
            @endforeach
        </ul>

        <p>&copy; {{ bn_digits(now()->year) }} {{ __('BijGhor') }}</p>
    </div>
</footer>
