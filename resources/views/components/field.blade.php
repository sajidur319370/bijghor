@props([
    'name',
    'label',
    'hint' => null,
    'optional' => false,
])

{{-- Label + input (slot) + hint + the field's validation error, in the current language. --}}
<div {{ $attributes }}>
    <label for="{{ $name }}" class="block font-semibold text-gray-800">
        {{ $label }}
        @if ($optional)
            <span class="font-normal text-gray-500">({{ __('optional') }})</span>
        @endif
    </label>
    <div class="mt-1">{{ $slot }}</div>
    @if ($hint)
        <p class="mt-1 text-sm text-gray-500" id="{{ $name }}-hint">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-sm font-semibold text-red-700" id="{{ $name }}-error">{{ $message }}</p>
    @enderror
</div>
