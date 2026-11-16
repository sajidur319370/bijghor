<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StaticPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function pages(): array
    {
        return [
            'about' => ['about', 'আমাদের কথা', 'About'],
            'contact' => ['contact', 'যোগাযোগ', 'Contact'],
            'terms' => ['terms', 'শর্তাবলি', 'Terms'],
            'privacy' => ['privacy', 'গোপনীয়তা', 'Privacy'],
            'refund policy' => ['refund-policy', 'ফেরত নীতি', 'Refund policy'],
        ];
    }

    #[DataProvider('pages')]
    public function test_page_works_in_both_languages_and_says_it_is_a_demo(string $route, string $bnTitle, string $enTitle): void
    {
        $this->get(route($route))
            ->assertOk()
            ->assertSee('<h1 class="text-2xl font-semibold text-soil-600 md:text-3xl">'.$bnTitle.'</h1>', false)
            ->assertSee('এটি একটি ডেমো শপ');

        $this->withSession(['locale' => 'en'])
            ->get(route($route))
            ->assertOk()
            ->assertSee('<h1 class="text-2xl font-semibold text-soil-600 md:text-3xl">'.$enTitle.'</h1>', false)
            ->assertSee('This is a demo shop');
    }

    public function test_footer_links_to_every_page(): void
    {
        $response = $this->get(route('home'));

        foreach (['track', 'about', 'contact', 'terms', 'privacy', 'refund-policy'] as $route) {
            $response->assertSee('href="'.route($route).'"', false);
        }
    }

    public function test_contact_page_shows_only_the_details_that_are_configured(): void
    {
        config(['shop.contact' => [
            'phone' => '01712345678', 'whatsapp' => null, 'email' => 'hello@bijghor.example',
            'facebook' => null, 'address_en' => null, 'address_bn' => null,
        ]]);

        $this->get(route('contact'))
            ->assertSee('০১৭১২৩৪৫৬৭৮')
            ->assertSee('tel:01712345678', false)
            ->assertSee('hello@bijghor.example')
            ->assertDontSee('wa.me', false)
            ->assertDontSee('ফেসবুক');
    }

    public function test_contact_page_without_details_says_they_are_coming(): void
    {
        config(['shop.contact' => array_fill_keys(['phone', 'whatsapp', 'email', 'facebook', 'address_en', 'address_bn'], null)]);

        $this->get(route('contact'))
            ->assertSee('যোগাযোগের তথ্য শিগগিরই এখানে দেওয়া হবে।')
            ->assertDontSee('tel:', false);
    }
}
