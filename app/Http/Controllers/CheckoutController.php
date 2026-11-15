<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\DeliveryZone;
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
}
