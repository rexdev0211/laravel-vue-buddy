<?php

namespace App\Services;

use App\Services\Payments\TwoKCharge;
use App\User;
use App\Models\Payment\AppleSubscription;
use App\Services\Payments\ApplePaymentService;

class PaymentService
{
    /**
     * System User
     *
     * @var User
     */
    protected $user;

    public function __construct()
    {

    }

    /**
     * Renew a subscription
     *
     * @return bool
     * @throws \Exception
     */
    public function renewSubscription(): bool
    {
        $user = $this->getUser();
        $subscription = $user->lastSubscription();
        if ($subscription instanceof AppleSubscription) {
            $service = new ApplePaymentService;
            $service->setUser($this->user);
            $service->setTransactionReceipt($subscription->latest_reciept);
            $service->setTransactionId($subscription->transaction_id);
            $newSubscription = $service->handleReceipt();
            if (
                !empty($subscription->expires_at)
                &&
                !empty($newSubscription->expires_at)
                &&
                $newSubscription->expires_at > $subscription->expires_at
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getPackagesWithIssuers()
    {
        $activeIssuers = config('payments.__main.active_issuers');
        $generalPackagesData = config("payments.__main.packages");

        foreach ($generalPackagesData as $packageIndex => &$packageData) {
            foreach ($activeIssuers as $issuer) {
                $packageData['issuers'][$issuer] = config("payments.$issuer.packages.$packageIndex", null) !== null;
            }
        }

        return array_values($generalPackagesData);
    }

    /**
     * @param string $issuer
     * @param string $packageId
     *
     * @return array
     */
    public function getMergedPackageData(string $issuer, $packageId = null): array
    {
        $generalPackagesData = config("payments.__main.packages");
        $issuerPackagesData = config("payments.$issuer.packages");

        /* Overwrite general packages data with issuer packages data */
        foreach ($issuerPackagesData as $key => $packageData) {
            if (!isset($generalPackagesData[$key])) {
                $generalPackagesData[$key] = [];
            }

            foreach ($packageData as $packageDataKey => $value) {
                $generalPackagesData[$key][$packageDataKey] = $value;
            }
        }

        $packageData = collect($generalPackagesData)
            ->map(function($package){
                $package['title'] = trans("payments.{$package['key']}");
                return $package;
            });

        if ($packageId) {
            $packageData = $packageData->filter(function($package, $packageIndex) use ($packageId){
                return
                    $packageIndex == $packageId
                    ||
                    ($package['id'] ?? -1) == $packageId
                    ||
                    ($package['key'] ?? -1) == $packageId;
            });
        }

        return $packageId ?
            $packageData->first()
            :
            $packageData->all();
    }

    /**
     * @param string $host
     *
     * @return array
     */
    public function getPublicCredentials(string $host)
    {
        $twokchargeCredentials = config('payments.2000charge.credentials', []);
        $credentials = [
            '2000charge' => $twokchargeCredentials[$host]['public_key'] ?? null
        ];
        return $credentials;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
