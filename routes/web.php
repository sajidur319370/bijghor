<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/category/{slug}', [ShopController::class, 'category'])->name('category');
Route::get('/product/{slug}', [ShopController::class, 'product'])->name('product');
Route::get('/search', [ShopController::class, 'search'])->name('search');

// Language switch: /lang/bn or /lang/en (anything else is a 404)
Route::get('/lang/{locale}', function (string $locale) {
    session(['locale' => $locale]);

    return back(fallback: route('home'));
})->whereIn('locale', SetLocale::LOCALES)->name('lang');
