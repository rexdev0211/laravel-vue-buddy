<?php namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class GooglePostbacksLog extends Model
{
    const
        ACTION_PAYMENT = 'PAYMENT',
        ACTION_PAYMENT_FAILED = 'PAYMENT_FAILED',
        POSTBACK_REQUEST = 'POSTBACK_REQUEST',
        POSTBACK_REQUEST_FAILED = 'POSTBACK_REQUEST_FAILED',
        MANUALLY_UPDATED = 'MANUALLY_UPDATED',
        MANUAL_UPDATE_FAILED = 'MANUAL_UPDATE_FAILED';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'google_postbacks_log';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

}
