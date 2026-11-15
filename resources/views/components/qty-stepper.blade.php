@props([
    'name' => 'qty',
    'value' => 1,
    'min' => 1,
    'max' => 99,
    'autosubmit' => false,
])

@php($inputId = $name.'-'.Str::random(4))

{{-- Works without JavaScript as a plain number input; Alpine adds the − / + buttons.
     With autosubmit, every change submits the surrounding form (used in the cart). --}}
<div x-data="{
        qty: {{ (int) $value }}, min: {{ (int) $min }}, max: {{ (int) $max }}, autosubmit: {{ $autosubmit ? 'true' : 'false' }},
        changed() { if (this.autosubmit) { this.$nextTick(() => this.$root.closest('form').requestSubmit()) } },
    }"
    {{ $attributes->class('flex h-11 shrink-0 items-center overflow-hidden rounded-lg border border-gray-300 bg-white') }}>
    <button type="button" x-cloak @click="qty = Math.max(min, qty - 1); changed()" :disabled="qty <= min"
        class="flex size-11 items-center justify-center text-gray-700 hover:bg-gray-100 disabled:opacity-40">
        <x-heroicon-o-minus class="size-4" />
        <span class="sr-only">{{ __('Decrease quantity') }}</span>
    </button>

    <label for="{{ $inputId }}" class="sr-only">{{ __('Quantity') }}</label>
    <input id="{{ $inputId }}" type="number" name="{{ $name }}" value="{{ (int) $value }}" min="{{ (int) $min }}" max="{{ (int) $max }}"
        x-model.number="qty" @change="qty = Math.min(max, Math.max(min, qty || min)); changed()" inputmode="numeric"
        class="h-full w-12 border-0 text-center text-base [appearance:textfield] focus:ring-0 focus:outline-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">

    <button type="button" x-cloak @click="qty = Math.min(max, qty + 1); changed()" :disabled="qty >= max"
        class="flex size-11 items-center justify-center text-gray-700 hover:bg-gray-100 disabled:opacity-40">
        <x-heroicon-o-plus class="size-4" />
        <span class="sr-only">{{ __('Increase quantity') }}</span>
    </button>
</div>
