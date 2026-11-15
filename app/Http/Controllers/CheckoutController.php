<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\DeliveryZone;
use App\Services\Cart;
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

    public function store(CheckoutRequest $request): RedirectResponse
    {
        // Placing the order comes in the next step (PlaceOrder service).
        return back()->withInput();
    }
}
