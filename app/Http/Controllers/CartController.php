<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Cart $cart): View
    {
        $notices = $cart->sync();

        return view('cart.index', [
            'lines' => $cart->lines(),
            'subtotal' => $cart->subtotal(),
            'notices' => $notices,
        ]);
    }

    public function add(Request $request, Cart $cart): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:'.Cart::MAX_QUANTITY],
        ]);

        $productId = (int) $validated['product_id'];
        $wanted = ($cart->items()[$productId] ?? 0) + (int) ($validated['qty'] ?? 1);
        $inCart = $cart->add($productId, (int) ($validated['qty'] ?? 1));

        if ($inCart === 0) {
            return back()->with('error', __('Sorry, this seed is out of stock.'));
        }

        $name = Product::whereKey($productId)->first()?->name;

        return back()->with('success', $inCart < $wanted
            ? __('Only :count of :name are available. Your cart has :count now.', ['count' => bn_digits($inCart), 'name' => $name])
            : __(':name was added to your cart.', ['name' => $name]));
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:0', 'max:'.Cart::MAX_QUANTITY],
        ]);

        $cart->update((int) $validated['product_id'], (int) $validated['qty']);

        return redirect()->route('cart');
    }
}
