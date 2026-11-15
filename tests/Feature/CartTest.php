<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_a_product_puts_it_in_the_session_cart(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'name_bn' => 'টমেটো']);

        $this->from(route('product', $product->slug))
            ->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 2])
            ->assertRedirect(route('product', $product->slug))
            ->assertSessionHas('cart', [$product->id => 2])
            ->assertSessionHas('success', 'টমেটো কার্টে যোগ হয়েছে।');

        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 3])
            ->assertSessionHas('cart', [$product->id => 5]);
    }

    public function test_quantity_is_capped_at_the_stock_left(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 5])
            ->assertSessionHas('cart', [$product->id => 3])
            ->assertSessionHas('success', fn (string $message) => str_contains($message, '৩'));
    }

    public function test_out_of_stock_inactive_or_unknown_products_cannot_be_added(): void
    {
        $soldOut = Product::factory()->outOfStock()->create();
        $inactive = Product::factory()->inactive()->create();

        foreach ([$soldOut->id, $inactive->id, 999999] as $productId) {
            $this->post(route('cart.add'), ['product_id' => $productId])
                ->assertSessionHas('error', 'দুঃখিত, এই বীজের স্টক শেষ।');
        }

        $this->assertSame([], session('cart', []));
    }

    public function test_invalid_quantity_is_rejected(): void
    {
        $product = Product::factory()->create();

        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 100])
            ->assertSessionHasErrors('qty');
        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 0])
            ->assertSessionHasErrors('qty');
    }

    public function test_cart_page_shows_fresh_prices_and_subtotal(): void
    {
        app()->setLocale('bn');
        $product = Product::factory()->create(['name_bn' => 'লাল শাক', 'price' => 40, 'stock' => 50]);

        $this->withSession(['cart' => [$product->id => 2]]);
        $product->update(['price' => 50]);   // price changed after it was added

        $this->get(route('cart'))
            ->assertOk()
            ->assertSee('লাল শাক')
            ->assertSee('৳ ১০০')            // 2 × 50, not 2 × 40
            ->assertSee(route('checkout'), false);
    }

    public function test_update_changes_quantity_and_zero_removes_the_line(): void
    {
        $product = Product::factory()->create(['stock' => 20]);
        $this->withSession(['cart' => [$product->id => 2]]);

        $this->post(route('cart.update'), ['product_id' => $product->id, 'qty' => 7])
            ->assertRedirect(route('cart'))
            ->assertSessionHas('cart', [$product->id => 7]);

        $this->post(route('cart.update'), ['product_id' => $product->id, 'qty' => 0])
            ->assertSessionHas('cart', []);
    }

    public function test_cart_page_removes_unavailable_items_and_lowers_quantities_with_a_notice(): void
    {
        $gone = Product::factory()->create(['name_bn' => 'পুরোনো বীজ']);
        $low = Product::factory()->create(['name_bn' => 'তুলসী', 'stock' => 2]);
        $this->withSession(['cart' => [$gone->id => 1, $low->id => 5]]);
        $gone->update(['is_active' => false]);

        $this->get(route('cart'))
            ->assertSee('কার্টের একটি বীজ আর পাওয়া যাচ্ছে না')
            ->assertSee('তুলসী মাত্র ২টি বাকি')
            ->assertSessionHas('cart', [$low->id => 2]);
    }

    public function test_empty_cart_shows_empty_state(): void
    {
        $this->get(route('cart'))
            ->assertOk()
            ->assertSee('আপনার কার্ট খালি')
            ->assertDontSee(route('checkout'), false);
    }

    public function test_header_badge_counts_packets(): void
    {
        $a = Product::factory()->create();
        $b = Product::factory()->create();

        $this->withSession(['cart' => [$a->id => 2, $b->id => 3]])
            ->get(route('home'))
            ->assertSee('>৫</span>', false);
    }
}
