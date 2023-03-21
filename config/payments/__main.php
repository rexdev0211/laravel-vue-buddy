<?php

return [
    'active_issuers' => [
        env('PRIMARY_PAYMENT_ISSUER', 'segpay'),
        '2000charge',
        'sofort',
        'flexpay'
    ],
    'packages' => [
        'one-month' => [
            'key' => 'one_month',
            'months' => 1,
            'subtitle' => '',
            'price' => '9,99€',
            'amount' => 9.99,
            'translate' => 'one_month',
            'recurring' => true,
        ],
        'three-months' => [
            'key' => 'three_months',
            'months' => 3,
            'subtitle' => '6,66€/',
            'price' => '19,99€',
            'amount' => 19.99,
            'translate' => 'three_months',
            'recurring' => true,
        ],
        'twelve-months' => [
            'key' => 'twelve_months',
            'months' => 12,
            'subtitle' => '4,99€/',
            'price' => '59,99€',
            'amount' => 59.99,
            'translate' => 'twelve_months',
            'recurring' => false,
        ],
    ],
];
