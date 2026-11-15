<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Product::factory()->create(['name_en' => 'Tomato (Hybrid)', 'name_bn' => 'টমেটো (হাইব্রিড)']);
        Product::factory()->create(['name_en' => 'Red Amaranth', 'name_bn' => 'লাল শাক']);
        Product::factory()->create(['name_en' => '100% Organic Spinach', 'name_bn' => 'পালং শাক']);
    }

    public function test_search_matches_bangla_names(): void
    {
        $this->get(route('search', ['q' => 'শাক']))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->count() === 2)
            ->assertSee('লাল শাক')
            ->assertSee('পালং শাক')
            ->assertDontSee('টমেটো');
    }

    public function test_search_matches_english_names_ignoring_case(): void
    {
        $this->get(route('search', ['q' => 'tomato']))
            ->assertViewHas('products', fn ($products) => $products->count() === 1)
            ->assertSee('টমেটো (হাইব্রিড)');
    }

    public function test_percent_and_underscore_are_matched_literally(): void
    {
        $this->get(route('search', ['q' => '%']))
            ->assertViewHas('products', fn ($products) => $products->count() === 1);

        $this->get(route('search', ['q' => '_']))
            ->assertViewHas('products', fn ($products) => $products->count() === 0)
            ->assertSee('কোনো বীজ পাওয়া যায়নি');
    }

    public function test_search_never_shows_inactive_or_deleted_products(): void
    {
        Product::factory()->inactive()->create(['name_bn' => 'বন্ধ শাক']);
        Product::factory()->create(['name_bn' => 'মুছে ফেলা শাক'])->delete();

        $this->get(route('search', ['q' => 'শাক']))
            ->assertViewHas('products', fn ($products) => $products->count() === 2)
            ->assertDontSee('বন্ধ শাক')
            ->assertDontSee('মুছে ফেলা শাক');
    }

    public function test_empty_search_lists_all_seeds(): void
    {
        $this->get(route('search'))
            ->assertOk()
            ->assertSee('সব বীজ')
            ->assertViewHas('products', fn ($products) => $products->count() === 3);
    }

    public function test_search_box_keeps_the_term(): void
    {
        $this->get(route('search', ['q' => 'শাক']))
            ->assertSee('value="শাক"', false)
            ->assertSee('“শাক” এর ফলাফল');
    }
}
