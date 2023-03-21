<?php

namespace App\Services\Payments;

use App\User;
use App\Models\Payment\AppleSubscription;
use App\Models\Payment\ApplePostbacksLog;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class ApplePaymentService
{
    const
        PRODUCTION_ENDPOINT = 'https://buy.itunes.apple.com/verifyReceipt',
        SANDBOX_ENDPOINT = 'https://sandbox.itunes.apple.com/verifyReceipt';

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
    protected $transactionReceipt;

    /**
     * System User
     *
     * @var User
     */
    protected $user;

    /**
     * Response statuses list
     */
    protected $responseStatuses = [
        21000 => 'The request to the App Store was not made using the HTTP POST request method.',
        21001 => 'This status code is no longer sent by the App Store.',
        21002 => 'The data in the receipt-data property was malformed or the service experienced a temporary issue. Try again.',
        21003 => 'The receipt could not be authenticated.',
        21004 => 'The shared secret you provided does not match the shared secret on file for your account.',
        21005 => 'The receipt server was temporarily unable to provide the receipt. Try again.',
        21006 => 'This receipt is valid but the subscription has expired. When this status code is returned to your server, the receipt data is also decoded and returned as part of the response. Only returned for iOS 6-style transaction receipts for auto-renewable subscriptions.',
        21007 => 'This receipt is from the test environment, but it was sent to the production environment for verification.',
        21008 => 'This receipt is from the production environment, but it was sent to the test environment for verification.',
        21009 => 'Internal data access error. Try again later.',
        21010 => 'The user account cannot be found or has been deleted.',
    ];

    /**
     * Handle receipt
     *
     * @return AppleSubscription
     * @throws \Exception
     */
    public function handleReceipt(): AppleSubscription
    {
        // Sent request to Apple server
        $response = $this->sendValidationRequest();
        if (empty($response)) {
            $msg = 'No response from Apple API received';
            \Log::error('Apple payment: '.$msg);
            throw new \Exception($msg, 500);
        }
        if ($response->getStatusCode() !== 200) {
            $msg = $response->getBody()->getContents();
            \Log::error('Apple payment: '.$msg);
            throw new \Exception($msg, 500);
        }

        // Parse the response
        $response = json_decode($response->getBody()->getContents(), true);
        if ($response['status'] !== 0) {
            $errorMessage =
                $this->responseStatuses[$response['status']]
                ??
                'Apple API endpoint response with status: ' . $response['status'];
            \Log::error('Apple payment: '.$errorMessage);
            throw new \Exception($errorMessage, 500);
        }

        // Verify the last receipt
        $latestReceipt = $response['latest_receipt_info'][0] ?? null;
        $this->verifyReceipt($latestReceipt);

        // Find a product
        $productId = $latestReceipt['product_id'];
        $product = collect(config('payments.itunes.packages'))
            ->where('id', $productId)
            ->first();
        if (empty($product)) {
            $msg = "Unknown product id: $productId";
            \Log::error('Apple payment: '.$msg);
            throw new \Exception($msg, 422);
        }

        // Calculate Expiry Date
        if (empty($latestReceipt['expires_date'])) {
            $expiryDate = $this->calculateExpiryDate($product, $latestReceipt['purchase_date']);
        } else {
            $expiryDate = $latestReceipt['expires_date'];
        }

        if (!$expiryDate) {
            $msg = "Receipt expiry date is not set";
            \Log::error('Apple payment: '.$msg);
            throw new \Exception($msg, 422);
        }

        if (Carbon::now()->gt($expiryDate)) {
            $msg = "Receipt expiry date is in the past: $expiryDate";
            throw new \Exception($msg, 422);
        }

        // Persist the subscription
        $subscription = $this->persistSubscription($productId, $expiryDate);

        return $subscription;
    }

    /**
     * Persist new/existed subscription
     *
     * @param string $productId
     * @param string $expiryDate
     *
     * @return AppleSubscription
     */
    protected function persistSubscription(string $productId, string $expiryDate): AppleSubscription
    {
        $subscription = AppleSubscription::where('transaction_id', $this->transactionId)->first();
        if (empty($subscription)) {
            $subscription = new AppleSubscription();
            $subscription->transaction_id = $this->transactionId;
            $subscription->package_id = $productId;
            $subscription->user_id = $this->user->id;
        }
        $subscription->latest_reciept = $this->transactionReceipt;
        $subscription->package_id = $productId;
        $subscription->expires_at = Carbon::parse($expiryDate)->toDateTimeString();
        $subscription->save();

        return $subscription;
    }

    /**
     * Validate receipt
     *
     * @param array $receipt
     *
     * @return void
     * @throws \Exception
     */
    protected function verifyReceipt(array $receipt): void
    {
        if (empty($receipt)) {
            $msg = "No receipt found at Apple's server";
            \Log::error('Apple payment: '.$msg);
            throw new \Exception($msg, 422);
        }
        if (empty($receipt['product_id'])) {
            $msg = "Latest receipt doesn't contain product_id field";
            \Log::error('Apple payment: '.$msg);
            throw new \Exception($msg, 422);
        }
    }

    protected function sendValidationRequest(): ResponseInterface
    {
        $url = config('payments.itunes.credentials.sandbox_mode', true) ?
            self::SANDBOX_ENDPOINT
            :
            self::PRODUCTION_ENDPOINT;

        $client = new Client();
        $body = [
            'receipt-data' => $this->transactionReceipt,
            'password' => config('payments.itunes.credentials.shared_secret'),
            'exclude-old-transactions' => true,
        ];

        $response = $client->request('POST', $url, [
            'http_errors' => false,
            'body' => json_encode($body),
        ]);

        return $response;
    }

    /**
     * Calculate Expiry Date
     *
     * @param array $product
     * @param string $purchaseDate
     *
     * @return string
     */
    protected function calculateExpiryDate($product, $purchaseDate)
    {
        return Carbon::parse($purchaseDate)->addMonths($product['months'])->format('Y-m-d H:i:s e');
    }

    public function createLogEntry(array $payload, string $action): void
    {
        $logEntry = new ApplePostbacksLog();
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
    public function getTransactionReceipt(): string
    {
        return $this->transactionReceipt;
    }

    /**
     * @param string $transactionReceipt
     */
    public function setTransactionReceipt(string $transactionReceipt): void
    {
        $this->transactionReceipt = $transactionReceipt;
    }

    /**
     * Show env settings data.
     */
    public function testSettingsData()
    {
        return [
            'sandbox_mode'  => config('payments.itunes.credentials.sandbox_mode', true),
            'shared_secret' => config('payments.itunes.credentials.shared_secret'),
            'url'           => config('payments.itunes.credentials.sandbox_mode', true) ? self::SANDBOX_ENDPOINT : self::PRODUCTION_ENDPOINT,
        ];
    }
}
