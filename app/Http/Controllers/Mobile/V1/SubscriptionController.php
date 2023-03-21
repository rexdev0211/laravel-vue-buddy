<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Enum\ProTypes;
use App\Mail\PaypalSubscriptionNotification;
use App\Models\Payment\ApplePostbacksLog;
use App\Models\Payment\AppleSubscription;
use App\Models\Payment\GooglePostbacksLog;
use App\Models\Payment\GoogleSubscription;
use App\Models\Payment\PaypalLog;
use App\PromoCode;
use App\Services\EmailService;
use App\Services\Payments\ApplePaymentService;
use App\Services\Payments\Flexpay;
use App\Services\Payments\GooglePaymentService;
use App\Services\Payments\Segpay;
use App\Services\Payments\TwoKCharge;
use App\Services\PaymentService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Log;

class SubscriptionController extends Controller
{
    /**
     * @var EmailService
     */
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        if (\Helper::isApp()) {
            $this->middleware('auth');
        }
        $this->emailService = $emailService;
    }

    /**
     * @return JsonResponse
     */
    public function promocode(): JsonResponse
    {
        $user = auth()->user();

        $promoCode = PromoCode::whereCode(strtolower(request()->get('code')))
            ->whereStatus(1)
            ->where('expiration_time', '>', Carbon::now()->format('Y-m-d H:i:s'))
            ->where(function($subquery) {
                $subquery->where('limit', 0)
                    ->orWhereRaw('`limit` > used_count');
            })
            ->first();

        if ($promoCode) {
            $date = Carbon::parse('now')
                ->addMonths($promoCode->months)
                ->addWeeks($promoCode->weeks)
                ->addDays($promoCode->days)
                ->format('Y-m-d H:i:s');

            $user->upgradeToPro($date, ProTypes::COUPON);

            $promoCode->used_count = $promoCode->used_count + 1;
            $promoCode->save();

            return response()->json([
                'discreetMode' => $user->discreet_mode ? true : false,
            ]);
        }

        return response()->json([
            'error' => 'Invalid Code',
        ], 422);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function paypal(Request $request): JsonResponse
    {
        $data = $request->all();
        $user = auth()->user();

        $emailData = [
            'userID' => $user->id,
            'username' => $user->name,
            'paypalEmail' => $data['paypalEmail'],
            'registeredEmail' => $user->email,
            'duration' => ucfirst(str_replace("_", " ", $data['duration'])),
        ];

        $emailBody = '';
        $subject = "Paypal upgrade request";

        foreach ($emailData as $key => $value) {
            $emailBody .= '<strong>'.ltrim(preg_replace('/[E]/', ' $0', $key)).':</strong> <span>'.$value.'</span><br>';
        }

        $this->emailService->sendMail(config('services.paypal.email'), config('const.ADMIN_NAME'), $subject, $emailBody);

        PaypalLog::create([
            'user_id' => $emailData['userID'],
            'username' => $emailData['username'],
            'paypal_email' => $emailData['paypalEmail'],
            'duration' => $emailData['duration']
        ]);

        return response()->json('ok');
    }

    /**
     * Upgrade user to PRO
     *
     * @return JsonResponse
     */
    public function initiate(): JsonResponse
    {
        $issuer = request()->get('issuer');
        $package_id = request()->get('package_id');
        $data = request()->get('data');
        $redirectUrl = null;

        try {
            if ($issuer == 'flexpay') {
                $service = new Flexpay(request()->getHost());
                $service->setUser(request()->user());
                $redirectUrl = $service->getRedirectUrl($package_id);
            } elseif ($issuer == 'segpay') {
                $service = new Segpay();
                $service->setUser(request()->user());
                $redirectUrl = $service->getRedirectUrl($package_id);
            } elseif (
                $issuer == TwoKCharge::ISSUER_2000_CHARGE
                ||
                $issuer == TwoKCharge::ISSUER_2000_CHARGE_SOFORT
            ) {
                $service = new TwoKCharge(request()->getHost());
                $service->setData($data);
                $service->setUser(request()->user());
                $service->setIssuer($issuer);
                $service->setPackageId($package_id);
                $service->setIp(request()->ip());
                $service->commitTransaction();
                $redirectUrl = $service->getRedirectUrl();
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code    = $e->getCode();
            $payload = array_merge($data, ['error_message' => $message, 'error_code' => $code]);
            Log::critical('Payment failed', ['issuer' => $issuer, 'data' => $payload]);
            return response()->json([
                'error' => $message
            ], $code ?? 500);
        }

        return response()->json([
            'redirect' => $redirectUrl
        ]);
    }

    /**
     * Unlock payment system on payment cancel
     *
     * @return JsonResponse
     */
    public function unlock(): JsonResponse
    {
        auth()->user()->unblockPurchase();
        return response()->json('ok');
    }

    /**
     * Cancel user PRO status
     *
     * @return JsonResponse
     */
    public function cancel(): JsonResponse
    {
        /** @var User $user */
        $user = request()->user();
        if ($user->getIssuer() == 'flexpay') {
            try {
                $service = new Flexpay(request()->getHost());
                $service->setUser($user);
                $url = $service->getCancelUrl();
            } catch (\Exception $e){
                return response()->json([
                    'error' => $e->getMessage()
                ], $e->getCode());
            }
        }
        return response()->json([
            'redirect' => $url
        ]);
    }

    /**
     * Get Payment Methods
     *
     * @return JsonResponse
     */
    public function settings(): JsonResponse
    {
        $service = new PaymentService();
        return response()->json([
            'packages' => $service->getPackagesWithIssuers(),
            'credentials' => $service->getPublicCredentials(request()->getHost()),
            'sofort_countries' => TwoKCharge::$sofortCountryCodes,
            'payment_blocked' => (bool) auth()->user()->isPurchaseBlocked(),
        ]);
    }


    /**
     * Handle Flexpay postbacks:
     * - initial
     * - extend
     * - expiry
     * - rebill
     * - cancel
     * - uncancel
     */
    public function handleFlexpayPostback()
    {
        $data = request()->all();
        Log::channel('payments')->info('Flexpay request', ['data' => $data]);

        $service = new Flexpay(request()->getHost());

        try {
            $service->setPostbackData($data);
            $service->createLogEntry($data);
            if (!$service->postbackIsValid()) {
                throw new \Exception('ERROR: Invalid signature', 400);
            }
            $service->handle();
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            $code = $e->getCode();
            $data += ['error_message' => $message, 'error_code' => $code];
            $service->createLogEntry($data, 'error');
            Log::channel('payments')->critical('Flexpay payment failed', ['data' => $data]);
            throw new \Exception("ERROR: $message", 500);
        }
        return response('OK', 200);
    }

    /**
     * Catch Segpay postback
     */
    public function handleSegpayPostback()
    {
        $data = request()->all();
        Log::channel('payments')->info('Segpay request', ['data' => $data]);

        try {
            $segpay = new Segpay($data);
            $segpay->handle();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = $e->getCode();
            $data += ['error_message' => $message, 'error_code' => $code];
            Log::channel('payments')->critical('Segpay payment failed', ['data' => $data]);
            throw new \Exception("ERROR: $message", 500);
        }
        return response('ok', 200);
    }

    /**
     * Handle 2000charge postback
     */
    public function handleTwokchargePostback()
    {
        $data = request()->all();
        Log::channel('payments')->info('2000charge request', ['data' => $data]);

        try {
            $service = new TwoKCharge(request()->getHost());
            $service->setData($data);
            $service->handle();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = $e->getCode();
            $data += ['error_message' => $message, 'error_code' => $code];
            Log::channel('payments')->critical('2000charge payment failed', ['data' => $data]);
            throw new \Exception("ERROR: $message", 500);
        }
        return response('ok', 200);
    }

    /**
     * Handle Apple postback
     */
    public function handleApplePostback()
    {
        $data = request()->all();

        /* Check notification_type and verify receipt */
        $notificationType = $data['notification_type'] ?? null;

        $notificationTypesAllowed = ['CANCEL', 'CONSUMPTION_REQUEST', 'DID_CHANGE_RENEWAL_PREF', 'DID_CHANGE_RENEWAL_STATUS', 'DID_FAIL_TO_RENEW', 'DID_RECOVER', 'DID_RENEW', 'INITIAL_BUY', 'INTERACTIVE_RENEWAL', 'PRICE_INCREASE_CONSENT', 'REFUND', 'REVOKE'];
        if (!in_array($notificationType, $notificationTypesAllowed)) {
            Log::channel('payments')->info('Apple request [UNSUPPORTED_TYPE]', ['data' => $data]);
            throw new \Exception("[Apple postback] Unsupported notification type (".$notificationType.")", 404);
        } else {
            Log::channel('payments')->info('Apple request', ['data' => $data]);
        }

        $allowedToProcess = ['REFUND', 'REVOKE', 'DID_RECOVER', 'DID_RENEW', 'INTERACTIVE_RENEWAL'];
        if (in_array($notificationType, $allowedToProcess)) {
//            Log::channel('payments')->info('Apple request', ['data' => $data]);

            /* Get user via original_transaction_id */
            $originalTransactionId = $data['original_transaction_id'] ?? null;

            if (!$originalTransactionId && isset($data['unified_receipt']['latest_receipt_info'])) {
                $latestReciept = collect($data['unified_receipt']['latest_receipt_info'])->first();

                $originalTransactionId = $latestReciept['original_transaction_id'] ?? null;
            }

            if (!$originalTransactionId && isset($data['unified_receipt']['pending_renewal_info'])) {
                $latestReciept = collect($data['unified_receipt']['pending_renewal_info'])->first();

                $originalTransactionId = $latestReciept['original_transaction_id'] ?? null;
            }

            $subscription = AppleSubscription::where('transaction_id', $originalTransactionId)->orderBy('id', 'DESC')->first();

            if ($originalTransactionId && $subscription) {
                $transactionReceipt = $data['unified_receipt']['latest_receipt'] ?? null;
                if (!$transactionReceipt) {
                    Log::channel('payments')->info('Apple request [TRANSACTION_RECEIPT_MISSED] Transaction data missed found for transactionID: '.$originalTransactionId);
                    throw new \Exception("[Apple postback] Transaction data missed found for transactionID: ".$originalTransactionId, 404);
                }

                $user = User::find($subscription->user_id);
                if (!$user) {
                    Log::channel('payments')->info('Apple request [USER_NOT_FOUND] User not found for transactionID: '.$originalTransactionId);
                    throw new \Exception("[Apple postback] User not found for transactionID: ".$originalTransactionId, 404);
                }

                $payload = [
                    'notification_type'  => $notificationType,
                    'transactionId'      => $subscription->transaction_id,
                    'transactionReceipt' => $transactionReceipt,
                ];

                $service = new ApplePaymentService;
                $service->setUser($user);
                $service->setTransactionId($subscription->transaction_id);
                $service->setTransactionReceipt($transactionReceipt);

                /* Add latest receipt infromation and extend user PRO status if we missing latest receipt */
                try {
                    $verifiedReceipt = $service->handleReceipt();
                    $service->createLogEntry($payload, ApplePostbacksLog::POSTBACK_REQUEST);

                    /* Upgrade/Downgrade user */
                    $upgradeTypes = ['DID_RECOVER', 'DID_RENEW', 'INTERACTIVE_RENEWAL'];
                    $cancelTypes  = ['REFUND', 'REVOKE'];
                    if (in_array($notificationType, $upgradeTypes)) {
                        $user->upgradeToPro($verifiedReceipt->expires_at, ProTypes::PAID);
                    } else {
                        $user->downgrade();
                    }
                } catch (\Throwable $e) {
                    $message  = $e->getMessage();
                    $code     = $e->getCode();
                    $payload += ['error_message' => $message, 'error_code' => $code];
                    $service->createLogEntry($payload, ApplePostbacksLog::POSTBACK_REQUEST_FAILED);
                }
            } else {
                Log::channel('payments')->info('Apple request [SUBSCRIPTION_NOT_FOUND] Subscription not found for transactionID: '.$originalTransactionId);

                response(['error' => 'Subscription not found for transactionID: ' . $originalTransactionId], 404);
                //throw new \Exception("[Apple postback] Subscription not found for transactionID: ".$originalTransactionId, 404);
            }
        }

        return response('ok', 200);
    }

    /**
     * Handle Google postback
     */
    public function handleGooglePostback()
    {
        $decodedData = null;
        $data = request()->all();

        if (isset($data['testNotification'])) {
            Log::channel('payments')->info('Google request [TEST]', ['data' => $data]);
        }

        /* Decode message data */
        $base64Encoded = $data['message']['data'] ?? null;

        if ($base64Encoded) {
            $decodedData = json_decode(base64_decode($base64Encoded));
        }

        if ($decodedData) {
            $notificationData = $decodedData->subscriptionNotification ?? null;

            if ($notificationData) {
                /* Get user via original_transaction_id */
                $originalPurchaseToken = $notificationData->purchaseToken;

                if ($originalPurchaseToken) {
                    Log::channel('payments')->info('Google request', ['data' => $data, 'decoded' => $notificationData]);

                    if ($originalPurchaseToken) {
                        $notificationTypeId = $notificationData->notificationType;

                        $notificationTypes = [
                            1  => 'SUBSCRIPTION_RECOVERED',
                            2  => 'SUBSCRIPTION_RENEWED',
                            3  => 'SUBSCRIPTION_CANCELED',
                            4  => 'SUBSCRIPTION_PURCHASED',
                            5  => 'SUBSCRIPTION_ON_HOLD',
                            6  => 'SUBSCRIPTION_IN_GRACE_PERIOD',
                            7  => 'SUBSCRIPTION_RESTARTED',
                            8  => 'SUBSCRIPTION_PRICE_CHANGE_CONFIRMED',
                            9  => 'SUBSCRIPTION_DEFERRED',
                            10 => 'SUBSCRIPTION_PAUSED',
                            11 => 'SUBSCRIPTION_PAUSE_SCHEDULE_CHANGED',
                            12 => 'SUBSCRIPTION_REVOKED',
                            13 => 'SUBSCRIPTION_EXPIRED',
                        ];

                        /* Check notificationType and verify receipt */
                        $notificationType = $notificationTypes[$notificationTypeId] ?? null;

                        $notificationTypesAllowed = [
                            'SUBSCRIPTION_RECOVERED', 'SUBSCRIPTION_RENEWED',
                            'SUBSCRIPTION_CANCELED', 'SUBSCRIPTION_REVOKED', 'SUBSCRIPTION_EXPIRED',
                            'SUBSCRIPTION_PURCHASED',
                            'SUBSCRIPTION_ON_HOLD', 'SUBSCRIPTION_IN_GRACE_PERIOD',
                            'SUBSCRIPTION_RESTARTED',
                            'SUBSCRIPTION_PRICE_CHANGE_CONFIRMED',
                            'SUBSCRIPTION_DEFERRED',
                            'SUBSCRIPTION_PAUSED', 'SUBSCRIPTION_PAUSE_SCHEDULE_CHANGED',
                        ];
                        if (!in_array($notificationType, $notificationTypesAllowed)) {
                            throw new \Exception("[Google postback] Unsupported notification type (".$notificationType.") purchaseToken: ".$originalPurchaseToken, 404);
                        }

                        $allowedToProcess = [
                            'SUBSCRIPTION_RECOVERED',
                            'SUBSCRIPTION_RENEWED',
                            'SUBSCRIPTION_DEFERRED',
                            'SUBSCRIPTION_RESTARTED',
                            'SUBSCRIPTION_REVOKED',
                        ];
                        if (in_array($notificationType, $allowedToProcess)) {
                            $subscription = GoogleSubscription::where('purchase_token', $originalPurchaseToken)->orderBy('id', 'DESC')->first();

                            if ($subscription) {
                                $user = User::find($subscription->user_id);
                                if (!$user) {
                                    throw new \Exception("[Google postback] User not found for purchaseToken: ".$originalPurchaseToken, 404);
                                }

                                $payload = [
                                    'notification_type' => $notificationType,
                                    'transactionId'     => $subscription->transaction_id,
                                    'purchaseToken'     => $originalPurchaseToken,
                                ];

                                $service = new GooglePaymentService;
                                $service->setUser($user);
                                $service->setProductId($subscription->package_id);
                                $service->setTransactionId($subscription->transaction_id);
                                $service->setPurchaseToken($originalPurchaseToken);

                                /* Add latest receipt infromation and extend user PRO status if we missing latest receipt */
                                try {
                                    $verifiedReceipt = $service->handleReceipt();
                                    $service->createLogEntry($payload, GooglePostbacksLog::POSTBACK_REQUEST);

                                    /* Upgrade/Downgrade user */
                                    $upgradeTypes = ['SUBSCRIPTION_RECOVERED', 'SUBSCRIPTION_RENEWED', 'SUBSCRIPTION_RESTARTED', 'SUBSCRIPTION_DEFERRED'];
                                    $cancelTypes  = ['SUBSCRIPTION_REVOKED'];
                                    if (in_array($notificationType, $upgradeTypes)) {
                                        $user->upgradeToPro($verifiedReceipt->expires_at, ProTypes::PAID);
                                    } else {
                                        $user->downgrade();
                                    }
                                } catch (\Throwable $e) {
                                    $message  = $e->getMessage();
                                    $code     = $e->getCode();
                                    $payload += ['error_message' => $message, 'error_code' => $code];
                                    $service->createLogEntry($payload, GooglePostbacksLog::POSTBACK_REQUEST_FAILED);
                                }
                            } else {
                                \Log::info("[Google postback] ". $notificationType ." Subscription not found for purchaseToken: ".$originalPurchaseToken);
                                abort(404);
                            }
                        }
                    }
                }
            }
        }

        return response('ok', 200);
    }
}
