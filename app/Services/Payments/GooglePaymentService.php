<?php

namespace App\Services\Payments;

use App\Models\Payment\GooglePostbacksLog;
use App\Services\PaymentService;
use App\User;
use App\Models\Payment\GoogleSubscription;

use Carbon\Carbon;
use Google_Client as Client;
use Google_Service_AndroidPublisher as AndroidPublisher;
use Log;

class GooglePaymentService
{
    const PACKAGE_NAME = 'net.buddy';

    /**
     * Product id
     *
     * @var string
     */
    protected $productId;

    /**
     * Transaction id
     *
     * @var string
     */
    protected $transactionId;

    /**
     * Purchase token
     *
     * @var string
     */
    protected $purchaseToken;

    /**
     * System User
     *
     * @var User
     */
    protected $user;

    public function initClient(): Client
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('service_account.json'));
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(AndroidPublisher::ANDROIDPUBLISHER);
        return $client;
    }

    /**
     * Handle receipt
     *
     * @return ?GoogleSubscription
     * @throws \Exception
     */
    public function handleReceipt(): ?GoogleSubscription
    {
        $productId = $this->getProductId();
        $package = (new PaymentService())->getMergedPackageData('google', $productId);
        if (empty($package)) {
            throw new \Exception("Unknown product id: $productId", 422);
        }

        $client = $this->initClient();
        $service = new AndroidPublisher($client);
        $purchase = null;
        try {
            if ($package['recurring']) {
                $purchase = (array)$service->purchases_subscriptions->get(self::PACKAGE_NAME, $this->productId, $this->purchaseToken);
            } else {
                $purchase = (array)$service->purchases_products->get(self::PACKAGE_NAME, $this->productId, $this->purchaseToken);
            }
        } catch (\Throwable $e) {
            Log::error('Google API response', ['error' => $e->getMessage()]);
            throw new \Exception('Google API error: ' . $e->getMessage(), 500);
        }

        if ($package['recurring']) {
            $expiryDate = Carbon::createFromTimestampMs($purchase['expiryTimeMillis']);
        } else {
            $expiryDate = Carbon::createFromTimestampMs($purchase['purchaseTimeMillis'])
                ->addMonths($package['months']);
        }

        if (Carbon::now()->gt($expiryDate)) {
            throw new \Exception("Receipt expiry date is in the past: $expiryDate", 422);
        }

        // Persist the subscription
        $subscription = $this->persistSubscription($expiryDate);

        return $subscription;
    }

    /**
     * Persist new/existed subscription
     *
     * @param Carbon $expiryDate
     *
     * @return GoogleSubscription
     */
    protected function persistSubscription(Carbon $expiryDate): GoogleSubscription
    {
        $subscription = GoogleSubscription::where('transaction_id', $this->getTransactionId())->first();
        if (empty($subscription)) {
            $subscription = new GoogleSubscription();
            $subscription->transaction_id = $this->getTransactionId();
            $subscription->user_id = $this->getUser()->id;
        }
        $subscription->purchase_token = $this->getPurchaseToken();
        $subscription->package_id = $this->getProductId();
        $subscription->package_name = self::PACKAGE_NAME;
        $subscription->expires_at = $expiryDate;
        $subscription->save();

        return $subscription;
    }

    public function createLogEntry(array $payload, string $action): void
    {
        $logEntry = new GooglePostbacksLog();
        $logEntry->action = $action;
        $logEntry->user_id = $this->user->id;
        $logEntry->transaction_id = $this->transactionId;
        $logEntry->data = json_encode($payload);
        $logEntry->created_at = Carbon::now();
        $logEntry->save();
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

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId(string $transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return string
     */
    public function getPurchaseToken(): string
    {
        return $this->purchaseToken;
    }

    /**
     * @param string $purchaseToken
     */
    public function setPurchaseToken(string $purchaseToken): void
    {
        $this->purchaseToken = $purchaseToken;
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @param string $productId
     */
    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }
}
