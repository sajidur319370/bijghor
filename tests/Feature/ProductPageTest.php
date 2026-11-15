<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_shows_seed_details_in_bangla(): void
    {
        $category = Category::factory()->create(['name_bn' => 'সবজি বীজ', 'slug' => 'vegetable-seeds']);
        Product::factory()->for($category)->create([
            'slug' => 'tomato-hybrid-1g',
            'name_bn' => 'টমেটো (হাইব্রিড)',
            'packet_size_bn' => '১ গ্রাম',
            'sowing_season_bn' => 'সেপ্টেম্বর - নভেম্বর',
            'germination_rate' => 90,
            'days_to_harvest' => 75,
            'price' => 120,
            'sale_price' => 99,
            'stock' => 150,
        ]);

        $this->get(route('product', 'tomato-hybrid-1g'))
            ->assertOk()
            ->assertSee('<title>টমেটো (হাইব্রিড) — সবজি বীজ | বীজঘর</title>', false)
            ->assertSee('১ গ্রাম')
            ->assertSee('সেপ্টেম্বর - নভেম্বর')
            ->assertSee('৯০%')
            ->assertSee('৭৫ দিন')
            ->assertSee('৳ ৯৯')
            ->assertSee('স্টকে আছে')
            ->assertSee(route('category', 'vegetable-seeds'), false);
    }

    public function test_inactive_deleted_or_unknown_products_are_not_found(): void
    {
        Product::factory()->inactive()->create(['slug' => 'inactive-seed']);
        Product::factory()->create(['slug' => 'deleted-seed'])->delete();

        $this->get(route('product', 'inactive-seed'))->assertNotFound();
        $this->get(route('product', 'deleted-seed'))->assertNotFound();
        $this->get(route('product', 'no-such-seed'))->assertNotFound();
    }

    public function test_out_of_stock_product_shows_badge_and_cannot_be_added(): void
    {
        Product::factory()->outOfStock()->create(['slug' => 'mint']);

        $this->get(route('product', 'mint'))
            ->assertOk()
            ->assertSee('স্টক শেষ')
            ->assertSee('disabled', false)
            ->assertDontSee('কার্টে যোগ করুন');
    }

    public function test_low_stock_warning(): void
    {
        Product::factory()->create(['slug' => 'tulsi', 'stock' => 8]);

        $this->get(route('product', 'tulsi'))->assertSee('মাত্র ৮টি বাকি');
    }

    public function test_related_products_are_from_the_same_category_and_visible(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create(['slug' => 'main-seed']);
        Product::factory()->for($category)->count(5)->create();
        Product::factory()->for($category)->inactive()->create(['name_bn' => 'বন্ধ সম্পর্কিত']);
        Product::factory()->create(['name_bn' => 'অন্য ক্যাটাগরি']);

        $this->get(route('product', 'main-seed'))
            ->assertViewHas('related', function ($related) use ($product, $category) {
                return $related->count() === 4
                    && $related->doesntContain('id', $product->id)
                    && $related->every(fn ($item) => $item->category_id === $category->id && $item->is_active);
            })
            ->assertDontSee('বন্ধ সম্পর্কিত')
            ->assertDontSee('অন্য ক্যাটাগরি');
    }

    public function test_gallery_shows_main_photo_then_gallery_photos_in_order(): void
    {
        $product = Product::factory()->create(['slug' => 'marigold', 'image' => 'products/main.webp']);
        $product->images()->create(['path' => 'products/second.webp', 'sort_order' => 2]);
        $product->images()->create(['path' => 'products/first.webp', 'sort_order' => 1]);

        $this->get(route('product', 'marigold'))
            ->assertSeeInOrder(['products/main.webp', 'products/first.webp', 'products/second.webp']);
    }
}
