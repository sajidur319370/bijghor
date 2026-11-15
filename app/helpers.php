<?php

/*
 * Global helpers (docs §5.7). Loaded through composer.json → autoload.files.
 */

const BN_DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
const EN_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

if (! function_exists('bn_digits')) {
    /**
     * Show digits in Bangla when the locale is bn: 120 → ১২০.
     */
    function bn_digits(string|int|float $value): string
    {
        return app()->getLocale() === 'bn'
            ? str_replace(EN_DIGITS, BN_DIGITS, (string) $value)
            : (string) $value;
    }
}

if (! function_exists('en_digits')) {
    /**
     * Turn Bangla digits typed by a visitor into 0-9: ০১৭ → 017.
     */
    function en_digits(?string $value): ?string
    {
        return $value === null ? null : str_replace(BN_DIGITS, EN_DIGITS, $value);
    }
}

if (! function_exists('money')) {
    /**
     * Taka amount for display: ৳ 1,250 (en) or ৳ ১,২৫০ (bn). Paisa only when not whole.
     */
    function money(string|int|float $amount): string
    {
        $value = (float) $amount;
        $decimals = fmod($value, 1.0) === 0.0 ? 0 : 2;

        return '৳ '.bn_digits(number_format($value, $decimals));
    }
}

if (! function_exists('normalize_phone')) {
    /**
     * Bangladeshi mobile number in the stored form: "+880 17-1234 5678" or "০১৭১২৩৪৫৬৭৮" → 01712345678.
     */
    function normalize_phone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', en_digits($phone));

        return str_starts_with($digits, '880') ? substr($digits, 2) : $digits;
    }
}
