<?php

/*
 * Bangla validation messages for the rules the storefront uses (docs §2.5).
 * Rules not listed here fall back to English (APP_FALLBACK_LOCALE).
 */
return [

    'email' => ':attribute সঠিক ইমেইল ঠিকানা হতে হবে।',
    'exists' => 'বেছে নেওয়া :attribute সঠিক নয়।',
    'integer' => ':attribute একটি পূর্ণ সংখ্যা হতে হবে।',
    'max' => [
        'numeric' => ':attribute :max এর বেশি হতে পারবে না।',
        'string' => ':attribute :max অক্ষরের বেশি হতে পারবে না।',
    ],
    'min' => [
        'numeric' => ':attribute কমপক্ষে :min হতে হবে।',
        'string' => ':attribute কমপক্ষে :min অক্ষরের হতে হবে।',
    ],
    'regex' => ':attribute এর ফরম্যাট সঠিক নয়।',
    'required' => ':attribute দিতে হবে।',
    'string' => ':attribute লেখা হতে হবে।',

    'custom' => [
        'phone' => [
            'regex' => 'বাংলাদেশি মোবাইল নম্বর দিন, যেমন ০১৭১২৩৪৫৬৭৮।',
        ],
    ],

    'attributes' => [
        'name' => 'নাম',
        'phone' => 'মোবাইল নম্বর',
        'email' => 'ইমেইল',
        'delivery_zone_id' => 'ডেলিভারি এলাকা',
        'district' => 'জেলা',
        'address' => 'ঠিকানা',
        'note' => 'নোট',
        'order_number' => 'অর্ডার নম্বর',
        'qty' => 'পরিমাণ',
    ],

];
