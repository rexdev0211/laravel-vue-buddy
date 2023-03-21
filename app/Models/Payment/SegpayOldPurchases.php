<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class SegpayOldPurchases extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'segpay_old_purchases';

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
        'updated_at',
    ];

}
