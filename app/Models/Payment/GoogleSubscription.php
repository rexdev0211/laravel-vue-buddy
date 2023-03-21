<?php namespace App\Models\Payment;

use App\Services\Payments\ApplePaymentService;
use Illuminate\Database\Eloquent\Model;

class GoogleSubscription extends Model
{
    public $issuerCodename = 'google';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'google_subscriptions';

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

    /**
     * Get Transaction Package name
     */
    public function getPackageName()
    {
        return trans('payments.' . $this->package_id);
    }
}
