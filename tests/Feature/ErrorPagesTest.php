<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_address_shows_a_404_in_both_languages(): void
    {
        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertSee('পাতাটি পাওয়া যায়নি')
            ->assertSee('Page not found')
            ->assertSee('ডেমো শপ')                          // full storefront layout
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_a_hidden_product_shows_the_same_404(): void
    {
        Product::factory()->inactive()->create(['slug' => 'hidden-seed']);

        $this->get(route('product', 'hidden-seed'))
            ->assertNotFound()
            ->assertSee('পাতাটি পাওয়া যায়নি');
    }

    public function test_too_many_tries_page_is_bilingual(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->get(route('track'));
        }

        $this->get(route('track'))
            ->assertTooManyRequests()
            ->assertSee('অনেক বেশি চেষ্টা হয়েছে')
            ->assertSee('Too many tries');
    }

    public function test_server_error_page_stands_alone_without_layout_or_database(): void
    {
        $this->view('errors.500')
            ->assertSee('দুঃখিত, একটি সমস্যা হয়েছে')
            ->assertSee('Sorry, something went wrong')
            ->assertDontSee('ডেমো শপ');
    }

    public function test_pages_have_favicons(): void
    {
        $this->get(route('home'))
            ->assertSee('favicon.svg', false)
            ->assertSee('apple-touch-icon.png', false);

        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));
    }
}
