<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_shows_only_its_own_visible_products(): void
    {
        $category = Category::factory()->create(['slug' => 'vegetable-seeds', 'name_bn' => 'সবজি বীজ']);
        Product::factory()->for($category)->create(['name_bn' => 'টমেটো']);
        Product::factory()->for($category)->inactive()->create(['name_bn' => 'বন্ধ পণ্য']);
        Product::factory()->for($category)->create(['name_bn' => 'মুছে ফেলা পণ্য'])->delete();
        Product::factory()->create(['name_bn' => 'অন্য ক্যাটাগরির পণ্য']);

        $this->get(route('category', 'vegetable-seeds'))
            ->assertOk()
            ->assertSee('<title>সবজি বীজ | বীজঘর</title>', false)
            ->assertSee('টমেটো')
            ->assertDontSee('বন্ধ পণ্য')
            ->assertDontSee('মুছে ফেলা পণ্য')
            ->assertDontSee('অন্য ক্যাটাগরির পণ্য');
    }

    public function test_inactive_or_unknown_category_is_not_found(): void
    {
        Category::factory()->inactive()->create(['slug' => 'hidden']);

        $this->get(route('category', 'hidden'))->assertNotFound();
        $this->get(route('category', 'does-not-exist'))->assertNotFound();
    }

    public function test_price_sorting_uses_the_sale_price_when_there_is_one(): void
    {
        $category = Category::factory()->create(['slug' => 'herbs']);
        Product::factory()->for($category)->create(['name_en' => 'Basil', 'price' => 50]);
        Product::factory()->for($category)->create(['name_en' => 'Mint', 'price' => 200, 'sale_price' => 30]);
        Product::factory()->for($category)->create(['name_en' => 'Coriander', 'price' => 80]);

        $this->withSession(['locale' => 'en'])
            ->get(route('category', ['slug' => 'herbs', 'sort' => 'price_asc']))
            ->assertSeeInOrder(['Mint', 'Basil', 'Coriander']);

        $this->get(route('category', ['slug' => 'herbs', 'sort' => 'price_desc']))
            ->assertSeeInOrder(['Coriander', 'Basil', 'Mint']);
    }

    public function test_unknown_sort_value_falls_back_to_newest(): void
    {
        $category = Category::factory()->create(['slug' => 'flowers']);
        Product::factory()->for($category)->create(['name_en' => 'Older', 'created_at' => now()->subDay()]);
        Product::factory()->for($category)->create(['name_en' => 'Newer']);

        $this->withSession(['locale' => 'en'])
            ->get(route('category', ['slug' => 'flowers', 'sort' => 'name; DROP TABLE products']))
            ->assertOk()
            ->assertViewHas('sort', 'newest')
            ->assertSeeInOrder(['Newer', 'Older']);
    }

    public function test_category_shows_twelve_products_per_page(): void
    {
        $category = Category::factory()->create(['slug' => 'fruits']);
        Product::factory()->for($category)->count(13)->create();

        $this->get(route('category', 'fruits'))
            ->assertViewHas('products', fn ($products) => $products->count() === 12 && $products->lastPage() === 2)
            ->assertSee('২ পাতার ১ নম্বর');

        $this->get(route('category', ['slug' => 'fruits', 'page' => 2]))
            ->assertViewHas('products', fn ($products) => $products->count() === 1);
    }
}
