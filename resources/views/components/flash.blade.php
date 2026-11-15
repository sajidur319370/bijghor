{{-- Messages set with ->with('success', ...) or ->with('error', ...). Auto-hide after 4 seconds. --}}
@foreach (['success' => 'border-brand-600 bg-brand-50 text-brand-800', 'error' => 'border-red-600 bg-red-50 text-red-800'] as $type => $classes)
    @if (session($type))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.opacity
            role="{{ $type === 'error' ? 'alert' : 'status' }}" class="mx-auto mt-4 w-full max-w-6xl px-4">
            <p class="rounded-lg border-l-4 px-4 py-3 {{ $classes }}">{{ session($type) }}</p>
        </div>
    @endif
@endforeach
