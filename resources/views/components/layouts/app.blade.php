@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' | '.__('BijGhor') : __('BijGhor').' — '.__('Seeds for every garden') }}</title>

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
