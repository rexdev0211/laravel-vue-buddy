<?php namespace App\Services\Payments;

use App\Enum\ProTypes;
use App\Models\Payment\TwokchargeLogs;
use App\Models\Payment\TwokchargeTransactions;
use App\Services\PaymentService;
use App\User;
use Carbon\Carbon;
use cURL;
use Illuminate\Support\Arr;

class TwoKCharge
{
    const
        API_URL = 'https://api.2000charge.com/api',
        ISSUER_2000_CHARGE = '2000charge',
        ISSUER_2000_CHARGE_SOFORT = '2000charge-sofort';

    /**
     * Allowed country codes for Sofort payment
     */
    public static $sofortCountryCodes = [
        'AT', 'BE', 'DE', 'IT', 'NL', 'ES', 'SK'
    ];

    /**
     * Success redirect
     *
     * @var string|null
     */
    protected $successRedirect;

    /**
     * Cuncel redirect
     *
     * @var string|null
     */
    protected $cancelRedirect;

    /**
     * Credentials
     *
     * @var array
     */
    protected $credentials;

    /**
     * Issuer
     *
     * @var string|null
     */
    protected $issuer;

    /**
     * Data
     *
     * @var array|null
     */
    protected $data;

    /**
     * Package ID
     *
     * @var string|null
     */
    protected $packageId;

    /**
     * User
     *
     * @var User|null
     */
    protected $user;

    /**
     * Redirect url
     *
     * @var string|null
     */
    protected $redirectUrl;

    /**
     * Ip
     *
     * @var string|null
     */
    protected $ip;

    /** @var string */
    protected $host;

    /**
     * Construct 2000charge instance
     *
     * @param $host
     *
     * @throws \Exception
     */
    public function __construct(string $host)
    {
        $this->setHost($host);
        $this->setupCredentials();
        $this->setRedirectUrls();
    }

    /**
     * @throws \Exception
     */
    public function setupCredentials()
    {
        $domain = $this->getDomainByHost();
        $domainConfig = config("payments.2000charge.credentials")[$domain] ?? null;
        if (empty($domainConfig)) {
            throw new \Exception("2000charge credentials for domain \"$domain\" are not set", 500);
        }

        $this->setCredentials($domainConfig);
    }

    public function getDomainByHost(): string
    {
        $host = $this->getHost();
        $domain = preg_replace('/(.+\.)?(\w+\.(com|net))$/ui', '$2', $host);
        return $domain;
    }

    public function setRedirectUrls()
    {
        $this->setSuccessRedirect(config('app.url') . '/profile/pro');
        $this->setCancelRedirect(config('app.url') . '/profile/pro/failed');
    }

    /**
     * Create transaction from widget response
     */
    public function makeTransactionViaWidget()
    {
        $packageId = $this->getPackageId();
        $packageData = (new PaymentService)->getMergedPackageData('2000charge', $packageId);
        $data = $this->getData();
        $ip = $this->getIp();
        $successRedirect = $this->getSuccessRedirect();
        $cancelRedirect = $this->getCancelRedirect();

        $customer = [
            'firstName' => $data['FirstName'],
            'lastName' => $data['LastName'],
            'email' => $data['Email'],
            'country' => $data['Country'],
        ];

        $transaction = [
            'customer' => $customer,
            'amount' => $packageData['amount'] * 100,
            'currency' => 'EUR',
            'token' => $data['Token'],
            'ipaddress' => $ip,
            'redirectUrls' => [
                'returnUrl' => $successRedirect,
                'cancelUrl' => $cancelRedirect,
            ],
        ];

        $response = $this->makeRequest('post', '/transactions', $transaction);

        return $response;
    }

    /**
     * Create transaction with Sofort form data
     */
    public function makeTransactionViaSofort()
    {
        $packageId = $this->getPackageId();
        $packageData = (new PaymentService)->getMergedPackageData('2000charge', $packageId);
        $data = $this->getData();
        $ip = $this->getIp();
        $successRedirect = $this->getSuccessRedirect();
        $cancelRedirect = $this->getCancelRedirect();

        $customer = [
            'firstName' => $data['first_name'],
            'lastName' => $data['last_name'],
            'email' => $data['email'],
            'country' => $data['country'],
        ];

        $transaction = [
            'customer' => $customer,
            'amount' => $packageData['amount'] * 100,
            'currency' => 'EUR',
            'ipaddress' => $ip,
            'payment' => [
                'paymentOption' => 'sofortuberweisung',
                'holder' => $data['first_name'] . ' ' . $data['last_name'],
            ],
            'redirectUrls' => [
                'returnUrl' => $successRedirect,
                'cancelUrl' => $cancelRedirect,
            ],
        ];

        $response = $this->makeRequest('post', '/transactions', $transaction);

        return $response;
    }

    /**
     * Commit transaction
     *
     * @throws \Exception
     */
    public function commitTransaction(): void
    {
        $response = null;
        $issuer = $this->getIssuer();
        $user = $this->getUser();
        $packageId = $this->getPackageId();

        if ($issuer == self::ISSUER_2000_CHARGE_SOFORT) {
            $response = $this->makeTransactionViaSofort();
        }
        if ($issuer == self::ISSUER_2000_CHARGE) {
            $response = $this->makeTransactionViaWidget();
        }

        if (
            !empty($response)
            &&
            in_array($response->statusCode, [200, 201])
        ) {
            $logEntry = $this->saveTransaction(
                json_decode($response->body),
                $user->id,
                $packageId
            );
            $user->blockPurchase();
            $this->setRedirectUrl($logEntry->redirect);
        } else {
            $response = json_decode($response->body, true);
            throw new \Exception(
                $response['Message'] ?? $response['statusText'],
                $response['StatusCode'] ?? 400
            );
        }
    }

    /**
     * Make curl request
     */
    protected function makeRequest($method, $target, $data)
    {
        $credentials = $this->getCredentials();
        $curl = cURL::newRawRequest($method, self::API_URL . $target, json_encode($data))
            ->setHeader('Content-Type', 'application/json')
            ->setUser($credentials['secret_key']);

        return $curl->send();
    }

    /**
     * Save transaction data to DB
     */
    public function saveTransaction($response, $user_id, $package_id)
    {
        $log = $this->saveLog('initial', $response->id ?? null, $response, $user_id);
        $transaction = TwokchargeTransactions::where('transaction_id', $response->id)->first();
        if (!$transaction) {
            $transaction = new TwokchargeTransactions();
        }

        $transaction->transaction_id = $response->id;
        $transaction->package_id = $package_id;
        $transaction->customer_id = $response->customer->id;
        $transaction->user_id = $user_id;
        $transaction->email = $response->customer->email;
        $transaction->status = $response->status;
        $transaction->ip = $response->ipAddress;
        $transaction->log_id = $log->id;
        $transaction->amount = round($response->amount) / 100;
        $transaction->currency = $response->currency;
        $transaction->payment_option = $response->payment->paymentOption;
        $transaction->redirect = $response->redirectUrl;
        $transaction->save();

        return $transaction;
    }

    /**
     * Save response/webhook log
     */
    protected function saveLog($action, $transaction_id, $response, $user_id)
    {
        $log = new TwokchargeLogs();
        $log->action = $action;
        $log->user_id = $user_id;
        $log->transaction_id = $transaction_id;
        $log->transaction_at = date('Y-m-d H:i:s', strtotime($response->created ?? 'now'));
        $log->data = json_encode($response);
        $log->save();

        return $log;
    }

    public function handle()
    {
        $data = $this->getData();

        $log = $this->saveLog(
            $data['type'] ?? 'none',
            $data['resource']['id'] ?? null,
            (object)$data,
            null
        );

        $transaction = $this->updateTransaction(
            $data['resource']['id'],
            $data['resource']['status'],
            $log,
            $data['resource']
        );

        switch ($data['type']) {
            // All transactions below just telling us that only transaction status have changed
            // case 'void.declined':
            // case 'void.succeeded':
            // case 'refund.pending':
            // case 'refund.declined':
            // case 'transaction.isf':
            // case 'transaction.aborted':
            // case 'transaction.invalid':

            case 'transaction.funded':
            case 'transaction.approved':
                $this->upgradeUserToPro($transaction);
                break;

            case 'refund.succeeded':
            case 'transaction.canceled':
            case 'transaction.chargeback':
                $this->downgradeUser($transaction);
                break;

            case 'subscription.created':
            case 'subscription.canceled':
            case 'subscription.expired':
                // subscriptions is not implemented yet
                break;
        }
    }

    protected function updateTransaction($transaction_id, $status, $log, $response)
    {
        $transaction = TwokchargeTransactions::where('transaction_id', $transaction_id)->first();
        if (empty($transaction)) {
            $transaction = new TwokchargeTransactions();
            $transaction->transaction_id = $response['id'];
            $transaction->package_id = null;
            $transaction->customer_id = $response['customer']['id'] ?? '';
            $transaction->user_id = 0;
            $transaction->email = $response['customer']['email'] ?? '';
            $transaction->status = $response['status'] ?? $status;
            $transaction->ip = $response['ipAddress'] ?? '';
            $transaction->log_id = $log->id;
            $transaction->amount = round($response['amount'] ?? 0) / 100;
            $transaction->currency = $response['currency'];
            $transaction->payment_option = $response['payment']['paymentOption'] ?? '';
            $transaction->redirect = $response['redirectUrl'] ?? '';
        } else {
            $transaction->status = $status;
        }
        $transaction->save();

        return $transaction;
    }

    /**
     * Upgrade user to PRO
     */
    protected function upgradeUserToPro($transaction)
    {
        if ($transaction->user_id) {
            $user = User::find($transaction->user_id);
            $packageData = (new PaymentService)->getMergedPackageData('2000charge', $transaction->package_id);
            if (
                !empty($user)
                &&
                !empty($packageData)
            ) {
                $expiryDate = Carbon::now()->addMonths($packageData['months'])->format('Y-m-d H:i:s');
                $user->upgradeToPro($expiryDate, ProTypes::PAID);
            }
        }
    }

    /**
     * Downgrade the user
     */
    protected function downgradeUser($transaction)
    {
        if ($transaction->user_id) {
            $user = User::find($transaction->user_id);
            if ($user) {
                $user->downgrade();
            }
        }
    }

    /**
     * @return array
     */
    public static function getSofortCountryCodes(): array
    {
        return self::$sofortCountryCodes;
    }

    /**
     * @param array $sofortCountryCodes
     */
    public static function setSofortCountryCodes(array $sofortCountryCodes): void
    {
        self::$sofortCountryCodes = $sofortCountryCodes;
    }

    /**
     * @return string
     */
    public function getSuccessRedirect(): string
    {
        return $this->successRedirect;
    }

    /**
     * @param string $successRedirect
     */
    public function setSuccessRedirect(string $successRedirect): void
    {
        $this->successRedirect = $successRedirect;
    }

    /**
     * @return string
     */
    public function getCancelRedirect(): string
    {
        return $this->cancelRedirect;
    }

    /**
     * @param string $cancelRedirect
     */
    public function setCancelRedirect(string $cancelRedirect): void
    {
        $this->cancelRedirect = $cancelRedirect;
    }

    /**
     * @return string
     */
    public function getIssuer(): string
    {
        return $this->issuer;
    }

    /**
     * @param string $issuer
     */
    public function setIssuer(string $issuer): void
    {
        $this->issuer = $issuer;
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
    public function getPackageId(): ?string
    {
        return $this->packageId;
    }

    /**
     * @param string|null $packageId
     */
    public function setPackageId(?string $packageId): void
    {
        $this->packageId = $packageId;
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
     * @return string|null
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * @param string|null $ip
     */
    public function setIp(?string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * @param string|null $redirectUrl
     */
    public function setRedirectUrl(?string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return array
     */
    public function getCredentials(): array
    {
        return $this->credentials;
    }

    /**
     * @param array $credentials
     */
    public function setCredentials(array $credentials): void
    {
        $this->credentials = $credentials;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }
}
