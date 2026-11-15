@props([
    'tone' => 'neutral',
])

<span {{ $attributes->class([
    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
    'bg-sun-400 text-gray-900' => $tone === 'sale',
    'bg-gray-800 text-white' => $tone === 'stock',
    'bg-brand-100 text-brand-800' => $tone === 'success',
    'bg-gray-100 text-gray-700' => $tone === 'neutral',
]) }}>{{ $slot }}</span>
