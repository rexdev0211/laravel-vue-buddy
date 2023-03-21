<?php

return [
    'credentials' => [
        env('DOMAIN', 0) => [
            'id' => env('2000CHARGE_ID', null),
            'public_key' => env('2000CHARGE_PUBLIC_KEY', null),
            'secret_key' => env('2000CHARGE_SECRET_KEY', null),
        ],
    ],
    'packages' => [
        'three-months' => [
            'id' => env('2000CHARGE_THREE_MONTHS_PLAN_ID', 'pln_7a67219de849'),
        ],
        'twelve-months' => [
            'id' => env('2000CHARGE_TWELVE_MONTHS_PLAN_ID', 'pln_589919c24f95'),
        ]
    ]
];