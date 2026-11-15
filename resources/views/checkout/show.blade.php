@php
    $inputClass = 'block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-base focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 focus:outline-none aria-invalid:border-red-600';
    $textareaClass = 'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 focus:outline-none aria-invalid:border-red-600';
    $selectedZone = old('delivery_zone_id');
    $selectedCharge = (float) ($zones->firstWhere('id', (int) $selectedZone)?->charge ?? 0);
@endphp

<x-layouts.app :title="__('Checkout')" :noindex="true">
    <div class="mx-auto max-w-6xl px-4 py-6">
        <h1 class="text-2xl font-semibold text-soil-600">{{ __('Checkout') }}</h1>

        @foreach ($notices as $notice)
            <p role="status" class="mt-4 rounded-lg border-l-4 border-sun-400 bg-sun-400/15 px-4 py-3 text-gray-800">{{ $notice }}</p>
        @endforeach

        @error('cart')
            <p role="alert" class="mt-4 rounded-lg border-l-4 border-red-600 bg-red-50 px-4 py-3 text-red-800">{{ $message }}</p>
        @enderror

        {{-- The total shown here is only for display: the server recalculates every price. --}}
        <form method="POST" action="{{ route('checkout.store') }}" novalidate
            x-data="{
                zone: @js($selectedZone ? (string) $selectedZone : ''),
                charges: @js($zones->mapWithKeys(fn ($zone) => [(string) $zone->id => (float) $zone->charge])),
                subtotal: @js($subtotal),
                bangla: @js(app()->getLocale() === 'bn'),
                submitting: false,
                get charge() { return this.zone === '' ? null : this.charges[this.zone]; },
                get total() { return this.subtotal + (this.charge ?? 0); },
                money(amount) {
                    const text = '৳ ' + Number(amount).toLocaleString('en-US', { maximumFractionDigits: 2 });
                    return this.bangla ? text.replace(/[0-9]/g, (d) => '০১২৩৪৫৬৭৮৯'[d]) : text;
                },
            }"
            @submit="submitting = true"
            class="mt-6 grid gap-6 lg:grid-cols-3 lg:items-start">
            @csrf

            <div class="space-y-6 lg:col-span-2">
                <fieldset class="space-y-4 rounded-xl border border-gray-200 p-4">
                    <legend class="px-1 text-lg font-semibold text-soil-600">{{ __('Your details') }}</legend>

                    <x-field name="name" :label="__('Name')">
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="120" autocomplete="name"
                            @error('name') aria-invalid="true" aria-describedby="name-error" @enderror class="{{ $inputClass }}">
                    </x-field>

                    <x-field name="phone" :label="__('Mobile number')" :hint="__('We use this to confirm your order and to track it.')">
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel"
                            placeholder="01XXXXXXXXX" maxlength="20"
                            @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror class="{{ $inputClass }}">
                    </x-field>

                    <x-field name="email" :label="__('Email')" :optional="true">
                        <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email"
                            @error('email') aria-invalid="true" aria-describedby="email-error" @enderror class="{{ $inputClass }}">
                    </x-field>
                </fieldset>

                <fieldset class="space-y-4 rounded-xl border border-gray-200 p-4">
                    <legend class="px-1 text-lg font-semibold text-soil-600">{{ __('Delivery') }}</legend>

                    <div>
                        <p class="font-semibold text-gray-800" id="zone-label">{{ __('Delivery area') }}</p>
                        <div class="mt-1 grid gap-2 sm:grid-cols-2" role="radiogroup" aria-labelledby="zone-label">
                            @foreach ($zones as $zone)
                                <label class="flex min-h-11 cursor-pointer items-center justify-between gap-3 rounded-lg border border-gray-300 px-3 py-2 has-checked:border-brand-600 has-checked:bg-brand-50">
                                    <span class="flex items-center gap-2">
                                        <input type="radio" name="delivery_zone_id" value="{{ $zone->id }}" x-model="zone" @checked((string) $selectedZone === (string) $zone->id)
                                            class="size-4 accent-brand-600">
                                        <span>{{ $zone->name }}</span>
                                    </span>
                                    <span class="font-semibold">{{ money($zone->charge) }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('delivery_zone_id')
                            <p class="mt-1 text-sm font-semibold text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-field name="district" :label="__('District')">
                        <input id="district" name="district" type="text" value="{{ old('district') }}" required maxlength="80" autocomplete="address-level2"
                            @error('district') aria-invalid="true" aria-describedby="district-error" @enderror class="{{ $inputClass }}">
                    </x-field>

                    <x-field name="address" :label="__('Full address')" :hint="__('House, road, area and thana.')">
                        <textarea id="address" name="address" rows="3" required maxlength="500" autocomplete="street-address"
                            @error('address') aria-invalid="true" aria-describedby="address-error" @enderror class="{{ $textareaClass }}">{{ old('address') }}</textarea>
                    </x-field>

                    <x-field name="note" :label="__('Note for us')" :optional="true">
                        <textarea id="note" name="note" rows="2" maxlength="500"
                            @error('note') aria-invalid="true" aria-describedby="note-error" @enderror class="{{ $textareaClass }}">{{ old('note') }}</textarea>
                    </x-field>
                </fieldset>
            </div>

            <aside class="rounded-xl border border-gray-200 bg-brand-50 p-4 lg:sticky lg:top-36">
                <h2 class="text-lg font-semibold text-soil-600">{{ __('Your order') }}</h2>

                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($lines as $line)
                        <li class="flex justify-between gap-3">
                            <span>{{ $line['product']->name }} <span class="text-gray-500">× {{ bn_digits($line['quantity']) }}</span></span>
                            <span class="shrink-0">{{ money($line['line_total']) }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-4 space-y-1 border-t border-gray-300 pt-3">
                    <div class="flex justify-between">
                        <dt>{{ __('Subtotal') }}</dt>
                        <dd>{{ money($subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>{{ __('Delivery charge') }}</dt>
                        <dd x-text="charge === null ? '—' : money(charge)">{{ $selectedZone ? money($selectedCharge) : '—' }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-300 pt-2 text-lg font-semibold">
                        <dt>{{ __('Total') }}</dt>
                        <dd x-text="money(total)">{{ money($subtotal + $selectedCharge) }}</dd>
                    </div>
                </dl>

                <p class="mt-4 rounded-lg bg-white px-3 py-2 text-sm text-gray-700">
                    @if (config('services.payment.driver') === 'demo')
                        {{ __('Next you will see a demo payment page. No real money is charged.') }}
                    @else
                        {{ __('Next you will pay with bKash in test mode, using a test wallet. No real money is charged.') }}
                    @endif
                </p>

                <button type="submit" :disabled="submitting"
                    class="mt-4 flex min-h-11 w-full items-center justify-center rounded-lg bg-brand-600 px-4 font-semibold text-white hover:bg-brand-700 disabled:opacity-60">
                    <span x-show="! submitting">{{ __('Place order') }}</span>
                    <span x-show="submitting" x-cloak>{{ __('Placing order…') }}</span>
                </button>
            </aside>
        </form>
    </div>
</x-layouts.app>
