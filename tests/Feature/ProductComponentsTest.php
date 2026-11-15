<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductComponentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_shows_sale_price_and_struck_through_regular_price(): void
    {
        app()->setLocale('en');
        $product = Product::factory()->make(['price' => 120, 'sale_price' => 99]);

        $this->blade('<x-price :product="$product" />', ['product' => $product])
            ->assertSee('৳ 99')
            ->assertSeeInOrder(['<del', '৳ 120', '</del>'], false);
    }

    public function test_price_uses_bangla_digits_in_bangla(): void
    {
        app()->setLocale('bn');
        $product = Product::factory()->make(['price' => 120, 'sale_price' => null]);

        $this->blade('<x-price :product="$product" />', ['product' => $product])
            ->assertSee('৳ ১২০')
            ->assertDontSee('<del', false);
    }

    public function test_card_shows_name_packet_size_and_links_to_product(): void
    {
        app()->setLocale('bn');
        $product = Product::factory()->create(['name_bn' => 'টমেটো', 'packet_size_bn' => '১ গ্রাম', 'slug' => 'tomato']);

        $this->blade('<x-product-card :product="$product" />', ['product' => $product])
            ->assertSee('টমেটো')
            ->assertSee('১ গ্রাম')
            ->assertSee(route('product', 'tomato'), false);
    }

    public function test_out_of_stock_card_has_badge_and_disabled_button(): void
    {
        app()->setLocale('en');
        $product = Product::factory()->outOfStock()->create();

        $this->blade('<x-product-card :product="$product" />', ['product' => $product])
            ->assertSee('Out of stock')
            ->assertSee('disabled', false)
            ->assertDontSee('Add to cart');
    }

    public function test_sale_badge_shows_for_products_on_sale(): void
    {
        app()->setLocale('en');
        $product = Product::factory()->onSale(50)->create();

        $this->blade('<x-product-card :product="$product" />', ['product' => $product])
            ->assertSee('Sale');
    }

    public function test_image_uses_uploads_url_or_placeholder(): void
    {
        $withPhoto = Product::factory()->make(['image' => 'products/tomato.webp']);
        $withoutPhoto = Product::factory()->make(['image' => null]);

        $this->blade('<x-product-image :product="$product" />', ['product' => $withPhoto])
            ->assertSee('/uploads/products/tomato.webp', false)
            ->assertSee('loading="lazy"', false);

        $this->blade('<x-product-image :product="$product" />', ['product' => $withoutPhoto])
            ->assertSee('role="img"', false)
            ->assertDontSee('<img', false);
    }
}
