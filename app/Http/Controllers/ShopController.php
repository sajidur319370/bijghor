<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    /** @var list<string> */
    public const SORTS = ['newest', 'price_asc', 'price_desc'];

    /**
     * Category page: active products, sortable, 12 per page (docs §5.3).
     */
    public function category(Request $request, string $slug): View
    {
        $category = Category::active()->where('slug', $slug)->firstOrFail();

        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'newest';

        $query = $category->products()->active();

        match ($sort) {
            'price_asc' => $query->orderByRaw('COALESCE(sale_price, price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, price) DESC'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        return view('shop.category', compact('category', 'products', 'sort'));
    }

    /**
     * Product page with gallery and up to 4 related products from the same category (docs §5.4).
     */
    public function product(string $slug): View
    {
        $product = Product::active()
            ->with(['category', 'images'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('shop.product', compact('product', 'related'));
    }

    /**
     * Search by Bangla or English name (docs §5.5).
     */
    public function search(Request $request): View
    {
        $term = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $products = Product::active()
            ->when($term !== '', fn ($query) => $query->search($term))
            ->orderBy('name_en')
            ->paginate(24)
            ->withQueryString();

        return view('shop.search', compact('term', 'products'));
    }
}
