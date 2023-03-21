<?php

return [
    'credentials' => [
        'shared_secret' => env('ITUNES_SHARED_SECRET', 'd44a182f18bb4cb493dbff58ada9156b'),
        'sandbox_mode' => env('ITUNES_SANDBOX_MODE', true),
    ],
    'packages' => [
        'one-month' => [
            'id'     => env('ITUNES_ONE_MONTH_ID', 'buddy.pro.1m.abo'),
            'months' => 1,
        ],
        'three-months' => [
            'id'     => env('ITUNES_THREE_MONTH_ID', 'buddy.pro.3m.abo'),
            'months' => 3,
        ],
        'twelve-months' => [
            'id'     => env('ITUNES_TWELVE_MONTH_ID', 'buddy.pro.12m.abo'),
            'months' => 12,
        ]
    ]
];
