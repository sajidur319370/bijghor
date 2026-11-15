<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Home page: category tiles with product counts and the featured products (docs §5.1, §5.2).
     */
    public function __invoke(): View
    {
        $categories = Category::active()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('sort_order')
            ->get();

        $featured = Product::active()
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();

        return view('shop.home', compact('categories', 'featured'));
    }
}
