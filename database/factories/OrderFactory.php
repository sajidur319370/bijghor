<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * A pending, unpaid order for ৳ 458 (388 + 70 delivery), without items.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => Order::newOrderNumber(),
            'customer_name' => 'রহিম উদ্দিন',
            'customer_phone' => '017'.fake()->numerify('########'),
            'customer_email' => null,
            'district' => 'ঢাকা',
            'address' => 'বাড়ি ১২, রোড ৩, মিরপুর',
            'subtotal' => '388.00',
            'delivery_charge' => '70.00',
            'total' => '458.00',
            'payment_method' => 'bkash',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'locale' => 'bn',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (): array => ['payment_status' => 'paid', 'status' => 'confirmed', 'paid_at' => now()]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => ['status' => 'cancelled']);
    }
}
