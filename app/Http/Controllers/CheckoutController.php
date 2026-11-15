<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Services\Cart;
use App\Services\PlaceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Cart $cart): View|RedirectResponse
    {
        $notices = $cart->sync();

        if ($cart->isEmpty()) {
            return redirect()->route('cart');
        }

        return view('checkout.show', [
            'lines' => $cart->lines(),
            'subtotal' => $cart->subtotal(),
            'zones' => DeliveryZone::active()->orderBy('sort_order')->get(),
            'notices' => $notices,
        ]);
    }

    public function store(CheckoutRequest $request, Cart $cart, PlaceOrder $placeOrder): RedirectResponse
    {
        $order = $placeOrder->handle($request->validated(), $cart);

        return redirect()->route('order.pay', $order->order_number);
    }

    /** Thank-you page. Shows no personal details (name, phone, address), only what was ordered. */
    public function success(string $number): View|RedirectResponse
    {
        $order = Order::with('items')->where('order_number', $number)->firstOrFail();

        if ($order->payment_status !== 'paid' || $order->status === 'cancelled') {
            return redirect()->route('checkout.failed', $order->order_number);
        }

        return view('checkout.success', ['order' => $order]);
    }

    public function failed(string $number): View|RedirectResponse
    {
        $order = Order::with('items')->where('order_number', $number)->firstOrFail();

        if ($order->payment_status === 'paid' && $order->status !== 'cancelled') {
            return redirect()->route('checkout.success', $order->order_number);
        }

        return view('checkout.failed', ['order' => $order]);
    }
}
