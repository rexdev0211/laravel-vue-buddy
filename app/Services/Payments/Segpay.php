<?php

namespace App\Services\Payments;

use App\Enum\ProTypes;
use App\Models\Payment\SegpayPurchase;
use App\Models\Payment\SegpayOldPurchases;
use App\Models\Payment\SegpayPostbacksLog;
use App\Services\PaymentService;
use App\User;
use Carbon\Carbon;

class Segpay
{
    /**
     * Postback log
     *
     * @var SegpayPostbacksLog|null
     */
    protected $log;

    /**
     * Postback data
     *
     * @var array|null
     */
    protected $data;

    /**
     * Postback action
     *
     * @var string|null
     */
    protected $action;

    /**
     * System User
     *
     * @var User|null
     */
    protected $user;

    /**
     * System User id
     *
     * @var int|null
     */
    protected $user_id;

    /**
     * Construct segpay instance
     *
     * @param array|null $data
     */
    public function __construct(?array $data = null)
    {
        if (!empty($data)) {
            $this->data = $data;
            $this->action = $data['action'] ?? null;
            $this->user_id = isset($data['memberid']) ? $data['memberid'] : null;

            /* Check if it's an old purchase */
            if ($data['purchaseid']) {
                $oldPurchase = SegpayOldPurchases::where('purchase_id', $data['purchaseid'])->first();

                /* If it's old purchase we need to change user_id */
                if ($oldPurchase) {
                    $this->user_id = $oldPurchase->user_id;
                }
            }

            $this->user = $this->user_id ? User::find($this->user_id) : null;
        }
    }

    /**
     * Catch segpay postback
     */
    public function handle()
    {
        switch ($this->action) {
            case 'Auth':
                return $this->auth();
                break;
            case 'Void':
                return $this->void();
                break;
            case 'Probe':
                return $this->probe();
                break;
            case 'Enable':
                return $this->enable();
                break;
            case 'Cancel':
                return $this->cancel();
                break;
            case 'Disable':
                return $this->disable();
                break;
            case 'Reactivation':
                return $this->reactivate();
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Catch Auth Inquiry action
     */
    protected function auth()
    {
        $this->log = $this->saveToLog();
        $saved = $this->savePurchase();

        if ($saved && $this->user && $saved->approved) {
            $this->upgradeUserToPro($saved);
            return true;
        }

        return false;
    }

    /**
     * Catch Void Inquiry action
     */
    protected function void()
    {
        $this->log = $this->saveToLog();
        $saved = $this->savePurchase();

        if ($saved && $this->user && $saved->approved) {
            $this->user->downgrade();
            return true;
        }

        return false;
    }

    /**
     * Catch Probe Inquiry action
     */
    protected function probe()
    {
        $log = $this->saveToLog();

        return $log ? true : false;
    }

    /**
     * Catch Enable action
     */
    protected function enable()
    {
        $this->log = $this->saveToLog();
        $saved = $this->savePurchase();

        if ($saved && $this->user) {
            $this->upgradeUserToPro($saved);

            return true;
        }

        return false;
    }

    /**
     * Catch Cancel action
     */
    protected function cancel()
    {
        $this->log = $this->saveToLog();
        $saved = $this->savePurchase();

        return true;
    }

    /**
     * Catch Disable action
     */
    protected function disable()
    {
        $this->log = $this->saveToLog();
        $saved = $this->savePurchase();

        if ($saved && $this->user) {
            $this->user->downgrade();

            return true;
        }

        return false;
    }

    /**
     * Catch Reactivation action
     */
    protected function reactivate()
    {
        $this->log = $this->saveToLog();
        $saved = $this->savePurchase();

        if ($saved && $this->user) {
            $this->upgradeUserToPro($saved);

            return true;
        }

        return false;
    }

    /**
     * Save request to log table
     */
    protected function saveToLog()
    {
        $log = new SegpayPostbacksLog();

        $data = $this->data;
        $date = isset($data['transtime']) ? str_replace(' (GMT STANDARD TIME)', '', $data['transtime']) : null;

        $replaces = ['billzip', 'billaddr', 'billcity', 'ccfirst6', 'billstate'];
        foreach ($replaces as $replace) {
            if (isset($data[$replace]) && $data[$replace]) $data[$replace] = 'replaced';
        }

        $log->action = $this->action;
        $log->user_id = $this->user_id;
        $log->transaction_id = $data['tranid'] ?? null;
        $log->transaction_at = $date ? Carbon::parse($date) : null;
        $log->data = json_encode($data);
        $log->created_at = Carbon::parse('now');

        $log->save();

        return $log;
    }

    /**
     * Save purchase
     */
    protected function savePurchase()
    {
        $purchase = new SegpayPurchase();

        $data = $this->data;
        $date = isset($data['transtime']) ? str_replace(' (GMT STANDARD TIME)', '', $data['transtime']) : null;

        $purchase->action = $this->action;
        $purchase->email = isset($data['billemail']) ? $data['billemail'] : '';
        $purchase->phone = isset($data['billphone']) ? $data['billphone'] : '';
        $purchase->log_id = $this->log->id;
        $purchase->user_id = $this->user_id;
        $purchase->type = isset($data['trantype']) ? $data['trantype'] : 'none';
        $purchase->stage = isset($data['stage']) ? $data['stage'] : 'none';
        $purchase->approved = isset($data['approved']) ? (strtolower($data['approved']) == 'yes' ? true : false) : null;
        $purchase->type = isset($data['trantype']) ? $data['trantype'] : 'none';
        $purchase->price = isset($data['price']) ? $data['price'] : 0;
        $purchase->currency = isset($data['currencycode']) ? $data['currencycode'] : '';
        $purchase->ip = isset($data['ipaddress']) ? $data['ipaddress'] : '';
        $purchase->last_4 = isset($data['cclast4']) ? $data['cclast4'] : 0;
        $purchase->eticketid = isset($data['eticketid']) ? $data['eticketid'] : null;
        $purchase->transaction_id = isset($data['tranid']) ? $data['tranid'] : null;
        $purchase->transaction_global_id = isset($data['transguid']) ? $data['transguid'] : null;
        $purchase->transaction_at = $date ? Carbon::parse($date) : null;
        $purchase->next_bill_at = isset($data['nextbilldate']) ? Carbon::parse($data['nextbilldate']) : null;

        $purchase->save();

        return $purchase;
    }

    /**
     * Upgrade user to PRO
     */
    protected function upgradeUserToPro($saved)
    {
        if ($saved->next_bill_at) {
            $this->user->upgradeToPro($saved->next_bill_at->format('Y-m-d H:i:s'), ProTypes::PAID);
        } else {
            $explode = explode(':', $saved->eticketid);
            if (isset($explode[1])) {
                $package = collect(config('payments.segpay.packages'))->where('id', $explode[1])->first();
                $this->user->upgradeToPro(Carbon::now()->addMonths($package['months'])->format('Y-m-d H:i:s'), ProTypes::PAID);
            }
        }
    }

    public function getRedirectUrl(string $packageId): ?string
    {
        $packageData = (new PaymentService)->getMergedPackageData('segpay', $packageId);

        $url = null;
        $appUrl = config('app.url');
        $subline = 'Click here to get back to BareBuddy';
        $user = $this->getUser();

        if ($packageData) {
            $url =
                $packageData["redirect"] .
                "&memberid={$user->id}" .
                "&x-auth-link=$appUrl/profile/pro&x-auth-text=$subline&x-decl-link=$appUrl/profile/pro&x-decl-text=$subline";
        }

        return $url;
    }

    /**
     * @return SegpayPostbacksLog|null
     */
    public function getLog(): ?SegpayPostbacksLog
    {
        return $this->log;
    }

    /**
     * @param SegpayPostbacksLog|null $log
     */
    public function setLog(?SegpayPostbacksLog $log): void
    {
        $this->log = $log;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array|null $data
     */
    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string|null $action
     */
    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * @param int|null $user_id
     */
    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }
}
