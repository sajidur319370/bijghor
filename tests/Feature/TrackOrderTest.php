<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackOrderTest extends TestCase
{
    use RefreshDatabase;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = Order::factory()->paid()->create(['order_number' => 'BG-261115-7K3QD', 'customer_phone' => '01712345678']);
        $this->order->items()->create([
            'product_name_en' => 'Tomato', 'product_name_bn' => 'টমেটো', 'packet_size_en' => '1 g', 'packet_size_bn' => '১ গ্রাম',
            'unit_price' => 99, 'quantity' => 2, 'line_total' => 198,
        ]);
    }

    public function test_track_page_shows_the_form_and_demo_banner(): void
    {
        $this->get(route('track'))
            ->assertOk()
            ->assertSee('অর্ডার ট্র্যাক করুন')
            ->assertSee('ডেমো শপ');
    }

    public function test_right_number_and_phone_show_the_order(): void
    {
        $this->post(route('track'), ['order_number' => 'BG-261115-7K3QD', 'phone' => '01712345678'])
            ->assertOk()
            ->assertSee('BG-261115-7K3QD')
            ->assertSee('টমেটো')
            ->assertSee('পরিশোধিত')
            ->assertSee('aria-current="step"', false)
            ->assertDontSee('এখন পেমেন্ট করুন');
    }

    public function test_bangla_digits_lowercase_and_country_code_still_match(): void
    {
        $this->post(route('track'), ['order_number' => ' bg-২৬১১১৫-7k3qd ', 'phone' => '+880 1712-345678'])
            ->assertSee('টমেটো');

        $this->post(route('track'), ['order_number' => 'BG-261115-7K3QD', 'phone' => '০১৭১২৩৪৫৬৭৮'])
            ->assertSee('টমেটো');
    }

    public function test_wrong_phone_shows_not_found(): void
    {
        $this->post(route('track'), ['order_number' => 'BG-261115-7K3QD', 'phone' => '01899999999'])
            ->assertOk()
            ->assertSee('অর্ডার পাওয়া যায়নি')
            ->assertDontSee('টমেটো');
    }

    public function test_both_fields_are_required(): void
    {
        $this->post(route('track'), [])->assertSessionHasErrors(['order_number', 'phone']);
    }

    public function test_unpaid_order_offers_to_pay_now(): void
    {
        $unpaid = Order::factory()->create(['customer_phone' => '01811111111']);

        $this->post(route('track'), ['order_number' => $unpaid->order_number, 'phone' => '01811111111'])
            ->assertSee('এখন পেমেন্ট করুন')
            ->assertSee(route('order.pay', $unpaid->order_number), false);
    }

    public function test_tracking_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('track'), ['order_number' => 'BG-000000-AAAAA', 'phone' => '01700000000'])->assertOk();
        }

        $this->post(route('track'), ['order_number' => 'BG-000000-AAAAA', 'phone' => '01700000000'])
            ->assertTooManyRequests();
    }
}
