<?php namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class AppleSubscription extends Model
{
    public $issuerCodename = 'apple';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'apple_subscriptions';

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
        'expires_at',
    ];

    /**
     * Get Transaction Package name
     */
    public function getPackageName()
    {
        return trans('payments.' . $this->package_id);
    }
}
