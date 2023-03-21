<?php namespace App\Models\Payment;

use App\Services\PaymentService;
use Illuminate\Database\Eloquent\Model;

class TwokchargeTransactions extends Model
{
    public $issuerCodename = 'twokcharge';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'twokcharge_transactions';

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany('App\Models\Payment\TwokchargeLogs', 'transaction_id', 'transaction_id')
            ->orderBy('id', 'ASC');
    }

    /**
     * Get Transaction Package
     */
    public function getPackage()
    {
        $packageData = (new PaymentService)->getMergedPackageData('2000charge', $this->package_id);
        return $packageData;
    }

    /**
     * Get Transaction Package name
     */
    public function getPackageName()
    {
        $package = $this->getPackage();
        return $package ? trans('payments.' . $package['translate']) : null;
    }

}
