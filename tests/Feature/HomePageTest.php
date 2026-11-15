<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_only_active_featured_products(): void
    {
        Product::factory()->featured()->create(['name_bn' => 'দেখা যাবে']);
        Product::factory()->create(['name_bn' => 'বাছাই নয়']);
        Product::factory()->featured()->inactive()->create(['name_bn' => 'বন্ধ পণ্য']);
        Product::factory()->featured()->create(['name_bn' => 'মুছে ফেলা পণ্য'])->delete();

        $this->get(route('home'))
            ->assertOk()
            ->assertViewHas('featured', fn ($featured) => $featured->count() === 1)
            ->assertSee('দেখা যাবে')
            ->assertDontSee('বাছাই নয়')
            ->assertDontSee('বন্ধ পণ্য')
            ->assertDontSee('মুছে ফেলা পণ্য');
    }

    public function test_home_shows_at_most_eight_featured_products(): void
    {
        Product::factory()->featured()->count(10)->create();

        $this->get(route('home'))
            ->assertViewHas('featured', fn ($featured) => $featured->count() === 8);
    }

    public function test_category_tiles_count_only_visible_products(): void
    {
        $category = Category::factory()->create(['name_bn' => 'সবজি বীজ']);
        Product::factory()->count(2)->for($category)->create();
        Product::factory()->inactive()->for($category)->create();
        Product::factory()->for($category)->create()->delete();

        $this->get(route('home'))
            ->assertSee('সবজি বীজ')
            ->assertSee('২টি পণ্য');
    }

    public function test_home_shows_empty_state_when_nothing_is_featured(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('শিগগিরই নতুন বীজ আসছে');
    }
}
