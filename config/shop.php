<?php

/*
| Shop details shown on the Contact page and in the footer.
| Leave a value empty in .env and it is simply not shown (nothing is invented).
*/
return [

    'contact' => [
        'phone' => env('SHOP_PHONE'),           // e.g. 01712345678
        'whatsapp' => env('SHOP_WHATSAPP'),     // e.g. 8801712345678 (with country code, no +)
        'email' => env('SHOP_EMAIL'),
        'facebook' => env('SHOP_FACEBOOK'),     // full URL to the Facebook page
        'address_en' => env('SHOP_ADDRESS_EN'),
        'address_bn' => env('SHOP_ADDRESS_BN'),
    ],

    /** Date shown as "Last updated" on the policy pages. */
    'policies_updated_at' => '2026-11-16',

];
