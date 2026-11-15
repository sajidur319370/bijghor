@props([
    'order',
])

{{-- Items and totals of a placed order, from the saved snapshot (not today's prices). --}}
<div {{ $attributes->class('rounded-xl border border-gray-200 p-4 text-left') }}>
    <ul class="space-y-2 text-sm">
        @foreach ($order->items as $item)
            <li class="flex justify-between gap-3">
                <span>
                    {{ $item->product_name }}
                    <span class="text-gray-500">({{ $item->packet_size }}) × {{ bn_digits($item->quantity) }}</span>
                </span>
                <span class="shrink-0">{{ money($item->line_total) }}</span>
            </li>
        @endforeach
    </ul>

    <dl class="mt-3 space-y-1 border-t border-gray-200 pt-3">
        <div class="flex justify-between">
            <dt>{{ __('Subtotal') }}</dt>
            <dd>{{ money($order->subtotal) }}</dd>
        </div>
        <div class="flex justify-between">
            <dt>{{ __('Delivery charge') }}</dt>
            <dd>{{ money($order->delivery_charge) }}</dd>
        </div>
        <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-semibold">
            <dt>{{ __('Total') }}</dt>
            <dd>{{ money($order->total) }}</dd>
        </div>
    </dl>
</div>
