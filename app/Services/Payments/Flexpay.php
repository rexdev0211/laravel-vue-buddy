<?php namespace App\Services\Payments;

use App\Enum\ProTypes;
use App\Services\PaymentService;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Verotel\FlexPay\Brand as FlexpayBrand;
use Verotel\FlexPay\Client as FlexpayClient;
use App\Models\Payment\FlexpaySubscription;
use App\Models\Payment\FlexpayPostbacksLog;

class Flexpay
{
    const
        EVENT_INITIAL = 'initial',
        EVENT_EXTEND = 'extend',
        EVENT_EXPIRY = 'expiry',
        EVENT_REBILL = 'rebill',
        EVENT_CANCEL = 'cancel',
        EVENT_UNCANCEL = 'uncancel',
        EVENT_CREDIT = 'credit',

        TYPE_PURCHASE = 'purchase',
        TYPE_SUBSCRIPTION = 'subscription',

        CUSTOM_DATA_USER_ID = 'custom1',
        CUSTOM_DATA_SERVICE_TYPE = 'custom2';

    /** @var string */
    protected $type;

    /** @var string */
    protected $event;

    /** @var User */
    protected $user;

    /** @var string */
    protected $transactionId;

    /** @var string */
    protected $saleId;

    /** @var string */
    protected $period;

    /** @var string */
    protected $expiryDate;

    /** @var array */
    protected $postbackData;

    /** @var FlexpayClient */
    protected $client;

    /** @var array */
    protected $credentials;

    /** @var string */
    protected $host;

    /**
     * @param $host
     *
     * @throws \Exception
     */
    public function __construct(string $host)
    {
        $this->setHost($host);
        $this->setupCredentials();
        $this->initClient();
    }

    /** @throws \Exception */
    public function initClient()
    {
        $credentials = $this->getCredentials();
        $this->client = new FlexpayClient(
            $credentials['shop_id'],
            $credentials['signature_key'],
            FlexpayBrand::create_from_merchant_id($credentials['merchant_id'])
        );
    }

    /**
     * @throws \Exception
     */
    public function setupCredentials()
    {
        $domain = $this->getDomainByHost();
        $domainConfig = config("payments.flexpay.credentials")[$domain] ?? null;
        if (empty($domainConfig)) {
            throw new \Exception("Flexpay credentials for domain \"$domain\" are not set", 500);
        }

        $this->setCredentials($domainConfig);
    }

    public function getDomainByHost(): string
    {
        $host = $this->getHost();
        $domain = preg_replace('/(.+\.)?(\w+\.(com|net))$/ui', '$2', $host);
        return $domain;
    }

    /** @throws \Exception */
    public function postbackIsValid(): bool
    {
        return $this->client->validate_signature($this->postbackData);
    }

    public function handle()
    {
        if ($this->getTransactionId()) {
            $type = $this->getType();
            if ($type == self::TYPE_PURCHASE) {
                $this->handlePurchase();
            } elseif ($type == self::TYPE_SUBSCRIPTION) {
                $this->handleSubscription();
            }
        }
    }

    public function handlePurchase()
    {
        $period = CarbonInterval::make($this->getPeriod());
        $proExpiryDate = Carbon::now()->add($period);
        $this->getUser()->upgradeToPro($proExpiryDate, ProTypes::PAID);
        $this->persistSubscription($proExpiryDate);
    }

    public function handleSubscription()
    {
        $user = $this->getUser();
        switch ($this->getEvent()) {
            case self::EVENT_INITIAL:
            case self::EVENT_REBILL: {
                $user->upgradeToPro($this->getExpiryDate(), ProTypes::PAID);
                $this->persistSubscription($this->getExpiryDate());
                break;
            }
            case self::EVENT_EXTEND:
            case self::EVENT_UNCANCEL: {
                $user->upgradeToPro($this->getExpiryDate(), ProTypes::MANUAL);
                $this->persistSubscription($this->getExpiryDate());
                break;
            }
            case self::EVENT_CREDIT:
            case self::EVENT_EXPIRY:
            case self::EVENT_CANCEL: {
                $user->downgrade();
                $this->persistSubscription(Carbon::now());
                break;
            }
        }
    }

    /**
     * @return array
     */
    public function getPostbackData(): array
    {
        return $this->postbackData;
    }

    /**
     * @param array $postbackData
     */
    public function setPostbackData(array $postbackData): void
    {
        $this->postbackData = $postbackData;

        if (!empty($postbackData['type'])) {
            $this->setType($postbackData['type']);
        }

        if (!empty($postbackData['transactionID'])) {
            $this->setTransactionId($postbackData['transactionID']);
        }

        if (!empty($postbackData['saleID'])) {
            $this->setSaleId($postbackData['saleID']);
        }

        if (!empty($postbackData[self::CUSTOM_DATA_USER_ID])) {
            $user = User::find((int)$postbackData[self::CUSTOM_DATA_USER_ID]);
            if (empty($user)) {
                throw new \Exception('User Not found', 500);
            }
            $this->setUser($user);
        }

        if (!empty($postbackData['period'])) {
            $this->setPeriod($postbackData['period']);
        } elseif (!empty($postbackData[self::CUSTOM_DATA_SERVICE_TYPE])) {
            $serviceType = explode(':', $postbackData[self::CUSTOM_DATA_SERVICE_TYPE]);
            if (
                $serviceType[0] && $serviceType[0] == 'subscription'
                and
                $serviceType[1]
            ) {
                $this->setPeriod($serviceType[1]);
            }
        }

        if (!empty($postbackData['event'])) {
            $this->setEvent($postbackData['event']);
        }

        if (
            !empty($postbackData['nextChargeOn'])
            ||
            !empty($postbackData['expiresOn'])
        ) {
            $date = Carbon::parse($postbackData['nextChargeOn'] ?? $postbackData['expiresOn'])
                ->toDateTimeString();
            $this->setExpiryDate($date);
        }
    }

    public function createLogEntry(array $payload, string $action = null): void
    {
        $logEntry = new FlexpayPostbacksLog();

        if ($action || $this->getEvent()) {
            $logEntry->action = $action ?? $this->getEvent();
        }

        $logEntry->user_id        = $this->getUser()->id;
        $logEntry->transaction_id = $this->getTransactionId();
        $logEntry->data           = json_encode($payload);
        $logEntry->created_at     = Carbon::now();
        $logEntry->save();
    }

    /**
     * Persist new/existed subscription
     *
     * @param string $expiryDate
     *
     * @return FlexpaySubscription
     */
    protected function persistSubscription(string $expiryDate): FlexpaySubscription
    {
        $subscription = new FlexpaySubscription();
        $subscription->user_id        = $this->getUser()->id;
        $subscription->package_id     = $this->getPeriod();
        $subscription->sale_id        = $this->getSaleId();
        $subscription->package_name   = null;
        $subscription->transaction_id = $this->getTransactionId();
        $subscription->data           = json_encode($this->getPostbackData());
        $subscription->expires_at     = Carbon::parse($expiryDate)->toDateTimeString();
        $subscription->save();

        return $subscription;
    }

    /**
     * Get Payment Settings
     *
     * @param string $packageId
     *
     * @return string|null
     */
    public function getRedirectUrl(string $packageId): ?string
    {
        $packageData = (new PaymentService)->getMergedPackageData('flexpay', $packageId);
        $user = $this->getUser();
        $params = [
            'priceAmount' => $packageData['amount'],
            'priceCurrency' => 'EUR',
            'description' => $packageData['title'],
            'custom1' => $user->id,
            'custom2' => 'subscription:' . $packageData['period'],
            'email' => $user->email,
            'subscriptionType' => $packageData['subscriptionType'],
            'period' => $packageData['period'],
            'type' => $packageData['type']
        ];

        $url = null;
        if ($packageData['type'] == 'subscription') {
            $url = $this->client->get_subscription_URL($params);
        } else {
            $url = $this->client->get_purchase_URL($params);
        }

        return $url;
    }

    /**
     * Get subscription cancel url
     *
     * @return string
     * @throws \Exception
     */
    public function getCancelUrl(): ?string
    {
        $user = $this->getUser();
        $lastTransaction = $user->lastSubscription();
        if (!($lastTransaction instanceof FlexpaySubscription)) {
            throw new \Exception('ERROR: Last user`s subscription wasn`t made via Verotel. Abort.', 400);
        }
        if ($lastTransaction->package_id == 'P1Y') {
            throw new \Exception('ERROR: Cancelling 12-months subscription is not allowed. Abort.', 400);
        }

        $url = null;
        if (!empty($lastTransaction->sale_id)) {
            $url = $this->client->get_cancel_subscription_URL([
                'saleID' => $lastTransaction->sale_id
            ]);
        }

        return $url;
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
    public function getPeriod(): string
    {
        return $this->period;
    }

    /**
     * @param string $period
     */
    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getEvent(): ?string
    {
        return $this->event;
    }

    /**
     * @param string $event
     */
    public function setEvent(string $event): void
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getExpiryDate(): string
    {
        return $this->expiryDate;
    }

    /**
     * @param string $expiryDate
     */
    public function setExpiryDate(string $expiryDate): void
    {
        $this->expiryDate = $expiryDate;
    }

    /**
     * @return string
     */
    public function getTransactionId(): ?string
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
    public function getSaleId(): string
    {
        return $this->saleId;
    }

    /**
     * @param string $saleId
     */
    public function setSaleId(string $saleId): void
    {
        $this->saleId = $saleId;
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
