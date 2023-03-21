<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'sparkpostWebhook',
        'sparkpostGeneration',
        'payments/apple/postback',
        'payments/itunes/update',
        'payments/segpay/postback',
        'payments/twokcharge/webhook',
        'payments/flexpay/postback',
    ];
}
