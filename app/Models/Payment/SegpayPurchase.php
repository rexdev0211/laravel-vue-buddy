<?php namespace App\Models\Payment;

use App\Services\PaymentService;
use Illuminate\Database\Eloquent\Model;

class SegpayPurchase extends Model
{
    public $issuerCodename = 'segpay';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'segpay_purchases';

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
        'next_bill_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany('App\Models\Payment\SegpayPostbacksLog', 'transaction_id', 'transaction_id')
            ->orderBy('id', 'ASC');
    }

    /**
     * Get Transaction Package
     */
    public function getPackage()
    {
        $packageId = null;
        if (!empty($this->eticketid)) {
            $exploded = explode(':', $this->eticketid);
            $packageId = $exploded[1] ?? null;

            $packageData = (new PaymentService)->getMergedPackageData('segpay', $packageId);
            return $packageData;
        }

        return null;
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
