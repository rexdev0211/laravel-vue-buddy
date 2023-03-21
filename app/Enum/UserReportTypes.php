<?php

namespace App\Enum;

class UserReportTypes
{
    const HARASSMENT = 'harassment';
    const FAKE       = 'fake';
    const SPAM       = 'spam';
    const UNDER_AGE  = 'under_age';
    const OTHER      = 'other';
    const ILLEGAL    = 'illegal';

    /**
     * Get list of lead types
     * @return [type] [description]
     */
    public static function all() {
        return collect([
            (object) [
                'type' => self::HARASSMENT,
                'name' => 'Harassment',
            ],
            (object) [
                'type' => self::FAKE,
                'name' => 'Fake',
            ],
            (object) [
                'type' => self::SPAM,
                'name' => 'Spam',
            ],
            (object) [
                'type' => self::UNDER_AGE,
                'name' => 'Under age',
            ],
            (object) [
                'type' => self::OTHER,
                'name' => 'Other',
            ],
            (object) [
                'type' => self::ILLEGAL,
                'name' => 'Illegal',
            ],
        ]);
    }
}
