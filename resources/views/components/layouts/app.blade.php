@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
])

@php
    $pageTitle = $title ? $title.' | '.__('BijGhor') : __('BijGhor').' — '.__('Seeds for every garden');
    $pageDescription = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($description ?? __('Buy vegetable, flower, fruit and herb seeds online in Bangladesh. Every packet shows the sowing season and germination rate.')))), 160);
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $pageDescription }}">
        @if ($noindex)
            <meta name="robots" content="noindex, follow">
        @endif
        <meta name="theme-color" content="#2f7d32">

        {{-- Open Graph: link previews on Facebook / WhatsApp --}}
        <meta property="og:site_name" content="{{ __('BijGhor') }}">
        <meta property="og:type" content="{{ $type }}">
        <meta property="og:title" content="{{ $title ?? $pageTitle }}">
        <meta property="og:description" content="{{ $pageDescription }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:locale" content="{{ app()->getLocale() === 'bn' ? 'bn_BD' : 'en_US' }}">
        @if ($image)
            <meta property="og:image" content="{{ $image }}">
        @endif
        {{ $head ?? '' }}

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col bg-white leading-relaxed text-gray-800 antialiased">
        <x-header />
        <x-flash />

        <main class="flex-1">
            {{ $slot }}
        </main>

        <x-footer />
    </body>
</html>
