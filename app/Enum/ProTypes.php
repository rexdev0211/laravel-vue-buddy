<?php

namespace App\Enum;

class ProTypes
{
    const NONE    = 'none';
    const PAID    = 'paid';
    const MANUAL  = 'manual';
    const COUPON  = 'coupon';

    /**
     * Get list of lead types
     * @return [type] [description]
     */
    public static function all() {
        return collect([
            (object) [
                'type' => self::NONE,
                'name' => 'None',
            ],
            (object) [
                'type' => self::PAID,
                'name' => 'Paid',
            ],
            (object) [
                'type' => self::MANUAL,
                'name' => 'Manual',
            ],
            (object) [
                'type' => self::COUPON,
                'name' => 'Coupon',
            ],
        ]);
    }
}
