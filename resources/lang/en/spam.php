<?php

use App\Services\SpamService;

return [
    // Ghosting rules
    SpamService::REASON_MULTIREG => [
        'snippet' => 'Multi-reg',
        'reason' => 'Multiple registrations on the same ip (withing last 24 hrs)',
    ],
    SpamService::REASON_AFRICA => [
        'snippet' => 'Nigeria / Ghana',
        'reason' => 'Account was created from the Nigeria or Ghana IP range',
    ],
    SpamService::REASON_10MSG => [
        'snippet' => '10msg',
        'reason' => 'More than 10 similar messages sent within first 24 hrs',
    ],
    SpamService::REASON_BOUNCED => [
        'snippet' => 'email bounce',
        'reason' => 'New account with email_validation=bounce',
    ],

    // Suspending rules
    SpamService::REASON_ADDRESS => [
        'snippet' => 'Spandauer Str. 5',
        'reason' => 'Users address is \'Spandauer Str. 5, 10178 Berlin, Germany\'',
    ],
    SpamService::REASON_CONTENT => [
        'snippet' => 'URL',
        'reason' => 'Used blocked URL',
    ],
    SpamService::REASON_IP => [
        'snippet' => 'IP',
        'reason' => 'IP is blocked',
    ],
    SpamService::REASON_REPORTED => [
        'snippet' => 'Report',
        'reason' => 'Reported as a spammer',
    ],
    SpamService::REASON_LIMITS => [
        'snippet' => 'Messaging limits exceeded',
        'reason' => 'Messaging limits are exceeded',
    ],
    SpamService::REASON_SPAM_MAIL => [
        'snippet' => 'spammail',
        'reason' => 'spammail'
    ]
];