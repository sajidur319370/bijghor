<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    private DeliveryZone $zone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->zone = DeliveryZone::create(['name_en' => 'Inside Dhaka', 'name_bn' => 'ঢাকার ভিতরে', 'charge' => 70, 'sort_order' => 1]);
    }

    public function test_placing_an_order_saves_a_snapshot_reduces_stock_and_clears_the_cart(): void
    {
        $tomato = Product::factory()->create([
            'name_en' => 'Tomato', 'name_bn' => 'টমেটো', 'packet_size_en' => '1 g', 'packet_size_bn' => '১ গ্রাম',
            'price' => 120, 'sale_price' => 99, 'stock' => 10,
        ]);
        $spinach = Product::factory()->create(['price' => 45.5, 'stock' => 5]);

        $response = $this->withSession(['cart' => [$tomato->id => 3, $spinach->id => 2]])
            ->post(route('checkout.store'), $this->form(['phone' => '০১৭১২৩৪৫৬৭৮']));

        $order = Order::with('items')->sole();
        $response->assertRedirect(route('order.pay', $order->order_number))
            ->assertSessionHas('cart', fn ($cart) => $cart === null || $cart === []);

        $this->assertMatchesRegularExpression('/^BG-\d{6}-[A-HJ-NP-Z2-9]{5}$/', $order->order_number);
        $this->assertSame('01712345678', $order->customer_phone);
        $this->assertSame('pending', $order->status);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame('bn', $order->locale);
        $this->assertSame('388.00', $order->subtotal);          // 3 × 99 + 2 × 45.50
        $this->assertSame('70.00', $order->delivery_charge);
        $this->assertSame('458.00', $order->total);

        $line = $order->items->firstWhere('product_id', $tomato->id);
        $this->assertSame('টমেটো', $line->product_name_bn);
        $this->assertSame('১ গ্রাম', $line->packet_size_bn);
        $this->assertSame('99.00', $line->unit_price);           // sale price at order time
        $this->assertSame('297.00', $line->line_total);

        $this->assertSame(7, $tomato->fresh()->stock);
        $this->assertSame(3, $spinach->fresh()->stock);
    }

    public function test_later_price_changes_do_not_touch_old_orders(): void
    {
        $product = Product::factory()->create(['price' => 50, 'stock' => 10]);
        $this->withSession(['cart' => [$product->id => 1]])->post(route('checkout.store'), $this->form());

        $product->update(['price' => 80, 'name_bn' => 'নতুন নাম']);

        $line = Order::sole()->items()->sole();
        $this->assertSame('50.00', $line->unit_price);
        $this->assertNotSame('নতুন নাম', $line->product_name_bn);
    }

    public function test_stock_that_ran_out_after_adding_to_cart_stops_the_order(): void
    {
        $product = Product::factory()->create(['name_bn' => 'তুলসী', 'stock' => 5]);
        $this->withSession(['cart' => [$product->id => 3]]);
        $product->update(['stock' => 2]);   // someone else bought them meanwhile

        $this->from(route('checkout'))
            ->post(route('checkout.store'), $this->form())
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors(['cart' => 'তুলসী এর স্টক শেষ']);

        $this->assertSame(0, Order::count());
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_the_last_packet_can_only_be_bought_once(): void
    {
        $product = Product::factory()->create(['stock' => 1]);

        $this->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->form(['phone' => '01711111111']))
            ->assertSessionDoesntHaveErrors();

        // Second browser put the same last packet in its cart before the first one checked out
        $this->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->form(['phone' => '01822222222']))
            ->assertSessionHasErrors('cart');

        $this->assertSame(1, Order::count());
        $this->assertSame(0, $product->fresh()->stock);
    }

    public function test_a_phone_with_three_unpaid_orders_cannot_place_a_fourth(): void
    {
        $product = Product::factory()->create(['stock' => 50]);

        for ($i = 1; $i <= 3; $i++) {
            $this->withSession(['cart' => [$product->id => 1]])
                ->post(route('checkout.store'), $this->form())
                ->assertSessionDoesntHaveErrors();
        }

        $this->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), $this->form())
            ->assertSessionHasErrors('phone');

        $this->assertSame(3, Order::count());
    }

    public function test_empty_cart_cannot_be_ordered(): void
    {
        $this->post(route('checkout.store'), $this->form())
            ->assertSessionHasErrors(['cart' => 'কার্ট খালি']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function form(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'রহিম উদ্দিন',
            'phone' => '01712345678',
            'delivery_zone_id' => $this->zone->id,
            'district' => 'ঢাকা',
            'address' => 'বাড়ি ১২, রোড ৩, মিরপুর',
        ];
    }
}
