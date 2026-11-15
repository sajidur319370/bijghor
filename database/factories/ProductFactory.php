<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(2, true));

        return [
            'category_id' => Category::factory(),
            'name_en' => $name,
            'name_bn' => 'বীজ পণ্য '.fake()->unique()->numberBetween(1, 99999),
            'slug' => Str::slug($name),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'description_en' => fake()->sentence(),
            'description_bn' => 'ভালো মানের বীজ।',
            'packet_size_en' => '10 g',
            'packet_size_bn' => '১০ গ্রাম',
            'price' => fake()->numberBetween(20, 200),
            'sale_price' => null,
            'stock' => fake()->numberBetween(20, 200),
            'germination_rate' => fake()->numberBetween(70, 95),
            'sowing_season_en' => 'October - December',
            'sowing_season_bn' => 'অক্টোবর - ডিসেম্বর',
            'days_to_harvest' => fake()->numberBetween(30, 120),
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (): array => ['stock' => 0]);
    }

    public function onSale(int|float $salePrice): static
    {
        return $this->state(fn (array $attributes): array => [
            'price' => max($attributes['price'], $salePrice + 1),
            'sale_price' => $salePrice,
        ]);
    }
}
