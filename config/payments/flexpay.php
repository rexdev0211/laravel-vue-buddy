<?php

return [
    'credentials' => [
        env('DOMAIN', 0) => [
            'merchant_id' => env('FLEXPAY_MERCHANT_ID', 0),
            'shop_id' => env('FLEXPAY_SHOP_ID', 0),
            'signature_key' => env('FLEXPAY_SIGNATURE_KEY', ''),
        ],
    ],
    'packages' => [
        'one-month' => [
            'type' => 'subscription',
            'subscriptionType' => 'recurring',
            'period' => 'P1M',
        ],
        'three-months' => [
            'type' => 'subscription',
            'subscriptionType' => 'recurring',
            'period' => 'P3M',
        ],
        'twelve-months' => [
            'type' => 'purchase',
            'subscriptionType' => null,
            'period' => 'P1Y',
        ],
    ]
];