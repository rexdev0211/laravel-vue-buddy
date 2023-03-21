<?php namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class TwokchargeLogs extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'twokcharge_logs';

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
        'transaction_at',
    ];

}
