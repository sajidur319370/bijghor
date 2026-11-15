<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFormTest extends TestCase
{
    use RefreshDatabase;

    private DeliveryZone $dhaka;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dhaka = DeliveryZone::create(['name_en' => 'Inside Dhaka', 'name_bn' => 'ঢাকার ভিতরে', 'charge' => 70, 'sort_order' => 1]);
    }

    public function test_empty_cart_redirects_to_cart(): void
    {
        $this->get(route('checkout'))->assertRedirect(route('cart'));
    }

    public function test_checkout_page_shows_zones_and_order_summary(): void
    {
        $product = Product::factory()->create(['name_bn' => 'টমেটো', 'price' => 120, 'sale_price' => 99]);
        DeliveryZone::create(['name_en' => 'Hidden', 'name_bn' => 'বন্ধ এলাকা', 'charge' => 10, 'is_active' => false]);

        $this->withSession(['cart' => [$product->id => 2]])
            ->get(route('checkout'))
            ->assertOk()
            ->assertSee('ঢাকার ভিতরে')
            ->assertSee('৳ ৭০')
            ->assertDontSee('বন্ধ এলাকা')
            ->assertSee('টমেটো')
            ->assertSee('৳ ১৯৮');
    }

    public function test_missing_fields_show_bangla_messages(): void
    {
        $product = Product::factory()->create();

        $this->withSession(['cart' => [$product->id => 1]])
            ->from(route('checkout'))
            ->post(route('checkout.store'), [])
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors([
                'name' => 'নাম দিতে হবে।',
                'phone' => 'মোবাইল নম্বর দিতে হবে।',
                'delivery_zone_id' => 'ডেলিভারি এলাকা দিতে হবে।',
                'district' => 'জেলা দিতে হবে।',
                'address' => 'ঠিকানা দিতে হবে।',
            ]);
    }

    public function test_messages_are_english_when_english_is_chosen(): void
    {
        $this->withSession(['locale' => 'en'])
            ->post(route('checkout.store'), ['phone' => '12345'])
            ->assertSessionHasErrors([
                'phone' => 'Enter a Bangladeshi mobile number, like 01712345678.',
                'delivery_zone_id' => 'The delivery area field is required.',
            ]);
    }

    public function test_wrong_phone_format_is_rejected(): void
    {
        $this->post(route('checkout.store'), ['phone' => '01212345678'])   // 012 is not a mobile prefix
            ->assertSessionHasErrors(['phone' => 'বাংলাদেশি মোবাইল নম্বর দিন, যেমন ০১৭১২৩৪৫৬৭৮।']);
    }

    public function test_phone_in_bangla_digits_or_with_country_code_is_accepted(): void
    {
        foreach (['০১৭১২৩৪৫৬৭৮', '+880 1712-345678'] as $phone) {
            $this->post(route('checkout.store'), $this->validForm(['phone' => $phone]))
                ->assertSessionDoesntHaveErrors();
        }
    }

    public function test_inactive_delivery_zone_is_rejected(): void
    {
        $closed = DeliveryZone::create(['name_en' => 'Closed', 'name_bn' => 'বন্ধ', 'charge' => 0, 'is_active' => false]);

        $this->post(route('checkout.store'), $this->validForm(['delivery_zone_id' => $closed->id]))
            ->assertSessionHasErrors('delivery_zone_id');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validForm(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'রহিম উদ্দিন',
            'phone' => '01712345678',
            'delivery_zone_id' => $this->dhaka->id,
            'district' => 'ঢাকা',
            'address' => 'বাড়ি ১২, রোড ৩, মিরপুর',
        ];
    }
}
