<?php

return [
    'credentials' => [
        'client_id' => env('GOOGLE_CLIENT_ID', null),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', null),
        'client_redirect_url' => env('GOOGLE_CLIENT_REDIRECT_URL', null),
    ],
    'packages' => [
        'one-month' => [
            'id' => 'buddy.pro.1m.abo',
        ],
        'three-months' => [
            'id' => 'buddy.pro.3m.abo',
        ],
        'twelve-months' => [
            'id'        => 'buddy.pro.12m.abo',
            'recurring' => true,
        ]
    ]
];
