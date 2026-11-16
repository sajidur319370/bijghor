@php
    $contact = config('shop.contact');
    $address = app()->getLocale() === 'bn' ? ($contact['address_bn'] ?: $contact['address_en']) : ($contact['address_en'] ?: $contact['address_bn']);
    $hasAny = $contact['phone'] || $contact['whatsapp'] || $contact['email'] || $contact['facebook'] || $address;
@endphp

<x-page :title="__('Contact')">
    @if ($hasAny)
        <p>{{ __('We are happy to help with orders, seeds and growing questions.') }}</p>

        <ul class="list-none! pl-0!">
            @if ($contact['phone'])
                <li>{{ __('Phone') }}: <a href="tel:{{ $contact['phone'] }}">{{ bn_digits($contact['phone']) }}</a></li>
            @endif
            @if ($contact['whatsapp'])
                <li>WhatsApp: <a href="https://wa.me/{{ $contact['whatsapp'] }}" rel="noopener" target="_blank">{{ __('Message us') }}</a></li>
            @endif
            @if ($contact['email'])
                <li>{{ __('Email') }}: <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></li>
            @endif
            @if ($contact['facebook'])
                <li>Facebook: <a href="{{ $contact['facebook'] }}" rel="noopener" target="_blank">{{ __('Our Facebook page') }}</a></li>
            @endif
            @if ($address)
                <li>{{ __('Address') }}: {{ $address }}</li>
            @endif
        </ul>
    @else
        <p>{{ __('Contact details will be added here soon.') }}</p>
    @endif

    <h2>{{ __('About an order?') }}</h2>
    <p>{{ __('Please keep your order number ready.') }}</p>
    <p><a href="{{ route('track') }}">{{ __('Check your order status yourself') }}</a></p>
</x-page>
