<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_has_description_and_open_graph_tags(): void
    {
        $this->get(route('home'))
            ->assertSee('<meta name="description" content="বাংলাদেশে অনলাইনে সবজি', false)
            ->assertSee('<meta property="og:site_name" content="বীজঘর">', false)
            ->assertSee('<meta property="og:locale" content="bn_BD">', false)
            ->assertDontSee('noindex', false);
    }

    public function test_product_page_has_its_own_description_image_and_price(): void
    {
        Product::factory()->create([
            'slug' => 'marigold',
            'description_bn' => 'উজ্জ্বল কমলা গাঁদা ফুল।',
            'image' => 'products/marigold.webp',
            'price' => 60,
        ]);

        $this->get(route('product', 'marigold'))
            ->assertSee('<meta name="description" content="উজ্জ্বল কমলা গাঁদা ফুল।">', false)
            ->assertSee('<meta property="og:type" content="product">', false)
            ->assertSee('/uploads/products/marigold.webp', false)
            ->assertSee('<meta property="product:price:amount" content="60.00">', false)
            ->assertSee('<meta property="product:price:currency" content="BDT">', false);
    }

    public function test_product_without_description_gets_a_generated_one(): void
    {
        Product::factory()->create([
            'slug' => 'basil',
            'name_bn' => 'তুলসী',
            'packet_size_bn' => '২ গ্রাম',
            'sowing_season_bn' => 'ফেব্রুয়ারি - এপ্রিল',
            'description_bn' => null,
            'description_en' => null,
        ]);

        $this->get(route('product', 'basil'))
            ->assertSee('content="তুলসী বীজ, ২ গ্রাম প্যাকেট। বোনার মৌসুম: ফেব্রুয়ারি - এপ্রিল।"', false);
    }

    public function test_category_page_has_a_description(): void
    {
        $category = Category::factory()->create(['slug' => 'herbs', 'name_bn' => 'ভেষজ বীজ']);
        Product::factory()->for($category)->count(3)->create();

        $this->get(route('category', 'herbs'))
            ->assertSee('অনলাইনে ভেষজ বীজ কিনুন: ৩টি বীজ', false);
    }

    public function test_search_results_are_not_indexed(): void
    {
        $this->get(route('search', ['q' => 'test']))
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }
}
