<?php

return [
    'credentials' => [],
    'packages' => [
        'one-month' => [
            'id' => env('SEGPAY_ONE_MONTH_ID', 25094),
            'redirect' => 'https://secure2.segpay.com/billing/poset.cgi?x-eticketid=' . env('SEGPAY_PACKAGE_ID', 197812) . ':' . env('SEGPAY_ONE_MONTH_ID', 25094),
            'months' => 1,
        ],
        'three-months' => [
            'id' => env('SEGPAY_THREE_MONTHS_ID', 25105),
            'redirect' => 'https://secure2.segpay.com/billing/poset.cgi?x-eticketid=' . env('SEGPAY_PACKAGE_ID', 197812) . ':' . env('SEGPAY_THREE_MONTHS_ID', 25105),
            'months' => 3,
        ],
        'twelve-months' => [
            'id' => env('SEGPAY_TWELVE_MONTHS_ID', 25106),
            'redirect' => 'https://secure2.segpay.com/billing/poset.cgi?x-eticketid=' . env('SEGPAY_PACKAGE_ID', 197812) . ':' . env('SEGPAY_SIX_MONTHS_ID', 25106),
            'months' => 12,
        ],
    ]
];
