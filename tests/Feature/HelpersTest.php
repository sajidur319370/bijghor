<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_bn_digits_only_converts_in_bangla(): void
    {
        app()->setLocale('bn');
        $this->assertSame('১২০', bn_digits(120));

        app()->setLocale('en');
        $this->assertSame('120', bn_digits(120));
    }

    public function test_en_digits_converts_bangla_digits(): void
    {
        $this->assertSame('01712345678', en_digits('০১৭১২৩৪৫৬৭৮'));
        $this->assertNull(en_digits(null));
    }

    public function test_money_formats_taka_in_both_languages(): void
    {
        app()->setLocale('en');
        $this->assertSame('৳ 1,250', money('1250.00'));
        $this->assertSame('৳ 99.50', money(99.5));

        app()->setLocale('bn');
        $this->assertSame('৳ ১,২৫০', money(1250));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function phoneNumbers(): array
    {
        return [
            'already normal' => ['01712345678', '01712345678'],
            'bangla digits' => ['০১৭১২৩৪৫৬৭৮', '01712345678'],
            'country code with spaces' => ['+880 1712-345678', '01712345678'],
            'country code no plus' => ['8801712345678', '01712345678'],
        ];
    }

    #[DataProvider('phoneNumbers')]
    public function test_normalize_phone(string $input, string $expected): void
    {
        $this->assertSame($expected, normalize_phone($input));
    }
}
