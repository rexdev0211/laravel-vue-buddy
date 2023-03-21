<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class PaypalLog extends Model
{
    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * @var string
     */
    protected $table = 'paypal_log';

    protected $fillable = [
        'user_id',
        'username',
        'paypal_email',
        'duration'
    ];
}
