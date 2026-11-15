<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackController extends Controller
{
    /**
     * Guest order tracking (docs §5.10). Both the order number AND the phone must match,
     * so nobody can look up someone else's order. Rate limited in routes/web.php.
     */
    public function __invoke(Request $request): View
    {
        if ($request->isMethod('get')) {
            return view('track.index', ['order' => null, 'searched' => false]);
        }

        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $order = Order::with('items')
            ->where('order_number', mb_strtoupper(trim(en_digits($validated['order_number']))))
            ->where('customer_phone', normalize_phone($validated['phone']))
            ->first();

        return view('track.index', ['order' => $order, 'searched' => true]);
    }
}
