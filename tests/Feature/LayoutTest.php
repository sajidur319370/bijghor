<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_are_in_bangla_by_default(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<html lang="bn">', false)
            ->assertSee('বীজঘর');
    }

    public function test_switching_to_english_changes_the_page_language(): void
    {
        $this->from(route('home'))
            ->get(route('lang', 'en'))
            ->assertRedirect(route('home'));

        $this->get(route('home'))
            ->assertSee('<html lang="en">', false)
            ->assertSee('BijGhor')
            ->assertDontSee('বীজঘর');
    }

    public function test_unknown_language_is_not_found(): void
    {
        $this->get('/lang/fr')->assertNotFound();
    }

    public function test_header_lists_only_active_categories_in_order(): void
    {
        Category::factory()->create(['name_bn' => 'ফুলের বীজ', 'sort_order' => 2]);
        Category::factory()->create(['name_bn' => 'সবজি বীজ', 'sort_order' => 1]);
        Category::factory()->inactive()->create(['name_bn' => 'লুকানো ক্যাটাগরি']);

        $this->get(route('home'))
            ->assertSeeInOrder(['সবজি বীজ', 'ফুলের বীজ'])
            ->assertDontSee('লুকানো ক্যাটাগরি');
    }

    public function test_cart_badge_shows_item_count_in_bangla_digits(): void
    {
        $this->withSession(['cart' => [1 => 2, 2 => 1]])
            ->get(route('home'))
            ->assertSee('>৩</span>', false);
    }
}
