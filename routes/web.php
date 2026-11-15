<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DemoPaymentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TrackController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/category/{slug}', [ShopController::class, 'category'])->name('category');
Route::get('/product/{slug}', [ShopController::class, 'product'])->name('product');
Route::get('/search', [ShopController::class, 'search'])->name('search');

Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:5,1')->name('checkout.store');     // stops scripts hoarding stock

Route::get('/order/{number}/pay', [PaymentController::class, 'pay'])->name('order.pay');
Route::get('/bkash/callback', [PaymentController::class, 'callback'])->name('bkash.callback');
// Demo payment page. Always registered (so it is testable) but returns 404 unless PAYMENT_DRIVER=demo.
Route::get('/demo-pay/{paymentId}', DemoPaymentController::class)->name('demo-pay.show');
Route::get('/order/success/{number}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/order/failed/{number}', [CheckoutController::class, 'failed'])->name('checkout.failed');

Route::match(['get', 'post'], '/track', TrackController::class)
    ->middleware('throttle:10,1')->name('track');

// Language switch: /lang/bn or /lang/en (anything else is a 404)
Route::get('/lang/{locale}', function (string $locale) {
    session(['locale' => $locale]);

    return back(fallback: route('home'));
})->whereIn('locale', SetLocale::LOCALES)->name('lang');
