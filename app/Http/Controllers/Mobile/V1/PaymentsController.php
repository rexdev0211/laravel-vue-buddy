<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Enum\ProTypes;
use App\Models\Payment\ApplePostbacksLog;
use App\Models\Payment\GooglePostbacksLog;
use App\Services\Payments\GooglePaymentService;

use App\User;
use App\Services\Payments\ApplePaymentService;
use Log;

class PaymentsController extends Controller
{
    /**
     * Native iOS App purchase handling
     */
    public function handleApple()
    {
        $payload = request()->all();

        /** @var User $user */
        $user = auth()->user();
        if ($user->isPro()){
            return response()->json([
                'error' => 'User already is PRO or Staff'
            ], 422);
        }

        /* Apple and Google types available */
        $transactionId = $payload['transactionId'] ?? null;
        $transactionReceipt = $payload['transactionReceipt'] ?? null;

        $service = new ApplePaymentService;
        $service->setUser($user);
        $service->setTransactionId($transactionId);
        $service->setTransactionReceipt($transactionReceipt);

        try {
            $subscription = $service->handleReceipt();
            $service->createLogEntry($payload, ApplePostbacksLog::ACTION_PAYMENT);
            $user->upgradeToPro($subscription->expires_at, ProTypes::PAID);
            return response()->json($subscription, 200);

        } catch (\Throwable $e) {
            $message = $e->getMessage();
            $code = $e->getCode();
            $payload += ['error_message' => $message, 'error_code' => $code];
            $service->createLogEntry($payload, ApplePostbacksLog::ACTION_PAYMENT_FAILED);
            return response()->json($message, $code ?? 500);
        }
    }

    /**
     * Native Android App purchase handling
     */
    public function handleGoogle()
    {
        $payload = request()->all();

        /** @var User $user */
        $user = auth()->user();

        /* Apple and Google types available */
        $productId = $payload['productId'] ?? null;
        $transactionId = $payload['transactionId'] ?? null;
        $purchaseToken = $payload['purchaseToken'] ?? null;

        if ($user->isPro()){
            return response()->json([
                'error' => 'User already is PRO or Staff'
            ], 422);
        }

        $service = new GooglePaymentService;
        $service->setUser($user);
        $service->setTransactionId($transactionId);
        $service->setPurchaseToken($purchaseToken);
        $service->setProductId($productId);

        try {
            $subscription = $service->handleReceipt();
            $service->createLogEntry($payload, GooglePostbacksLog::ACTION_PAYMENT);
            $user->upgradeToPro($subscription->expires_at, ProTypes::PAID);
            return response()->json($subscription, 200);

        } catch (\Throwable $e) {
            $message = $e->getMessage();
            $code = $e->getCode();
            $payload += ['error_message' => $message, 'error_code' => $code];
            $service->createLogEntry($payload, GooglePostbacksLog::ACTION_PAYMENT_FAILED);
            Log::critical('Payment failed', ['data' => $payload]);
            return response()->json($message, $code ?? 500);
        }
    }
}
