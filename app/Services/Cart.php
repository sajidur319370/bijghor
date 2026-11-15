<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Guest cart kept in the session as [product_id => quantity] (docs §5.6).
 * Prices are never stored here: they are always read fresh from the database.
 */
class Cart
{
    public const MAX_QUANTITY = 99;

    private const SESSION_KEY = 'cart';

    public function __construct(private Session $session) {}

    /**
     * @return array<int, int> product_id => quantity
     */
    public function items(): array
    {
        return $this->session->get(self::SESSION_KEY, []);
    }

    /**
     * Adds to the quantity already in the cart. Returns the quantity now in the cart (0 = not available).
     */
    public function add(int $productId, int $quantity = 1): int
    {
        return $this->update($productId, ($this->items()[$productId] ?? 0) + $quantity);
    }

    /**
     * Sets the quantity, capped at the current stock and MAX_QUANTITY. 0 or less removes the line.
     * Returns the quantity now in the cart.
     */
    public function update(int $productId, int $quantity): int
    {
        $stock = (int) Product::active()->whereKey($productId)->value('stock');
        $quantity = max(0, min($quantity, $stock, self::MAX_QUANTITY));

        $items = $this->items();
        if ($quantity > 0) {
            $items[$productId] = $quantity;
        } else {
            unset($items[$productId]);
        }
        $this->session->put(self::SESSION_KEY, $items);

        return $quantity;
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    /** Total number of packets, for the header badge. */
    public function count(): int
    {
        return array_sum($this->items());
    }

    public function isEmpty(): bool
    {
        return $this->items() === [];
    }

    /**
     * Cart lines with fresh product data, in the order they were added.
     *
     * @return Collection<int, array{product: Product, quantity: int, line_total: float}>
     */
    public function lines(): Collection
    {
        $items = $this->items();
        $products = Product::active()->whereIn('id', array_keys($items))->get()->keyBy('id');

        return collect($items)
            ->filter(fn (int $quantity, int $productId) => $products->has($productId))
            ->map(fn (int $quantity, int $productId) => [
                'product' => $products[$productId],
                'quantity' => $quantity,
                'line_total' => (float) $products[$productId]->effective_price * $quantity,
            ])
            ->values();
    }

    public function subtotal(): float
    {
        return (float) $this->lines()->sum('line_total');
    }

    /**
     * Removes products that are no longer for sale and lowers quantities above the stock left.
     * Returns a visitor-facing message for every change.
     *
     * @return list<string>
     */
    public function sync(): array
    {
        $items = $this->items();
        if ($items === []) {
            return [];
        }

        $products = Product::active()->whereIn('id', array_keys($items))->get()->keyBy('id');
        $messages = [];

        foreach ($items as $productId => $quantity) {
            $product = $products->get($productId);

            if (! $product || $product->stock < 1) {
                unset($items[$productId]);
                $messages[] = $product
                    ? __(':name is out of stock and was removed from your cart.', ['name' => $product->name])
                    : __('A seed in your cart is no longer available and was removed.');
            } elseif ($quantity > $product->stock) {
                $items[$productId] = $product->stock;
                $messages[] = __('Only :count of :name left, so we lowered the quantity.', [
                    'count' => bn_digits($product->stock),
                    'name' => $product->name,
                ]);
            }
        }

        $this->session->put(self::SESSION_KEY, $items);

        return $messages;
    }
}
