<?php

namespace App\Console\Commands\Subscriptions;

use Carbon\Carbon;
use Illuminate\Console\Command;

use App\User;
use App\Enum\ProTypes;
use App\Models\Payment\GoogleSubscription;
use App\Models\Payment\GooglePostbacksLog;
use App\Services\Payments\GooglePaymentService;

class GoogleGetLatestReceipts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:google_get_latest_receipts {--debug= : Debug}
                                                                     {--detailed= : Show detailed logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscriptions: Get latest receipts for all subscriptions that we have for google';

    /**
     * The console command debug message key.
     *
     * @var string
     */
    protected $infoMessageKey = '[Subscriptions: Google Get Latest Receipts]';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        $usersProcessed = [];
        $this->showMsg('Started');

        /* Get all latest successfull Google payments for each user */
        $subscriptions = GoogleSubscription::where('expires_at', '<=', now()->addDays(2)->format('Y-m-d 23:59:59'))
                                           ->orderBy('created_at', 'DESC')
                                           ->get()
                                           ->groupBy('user_id');

        $this->showMsg($subscriptions->count().' subscriptions found.');
        foreach ($subscriptions as $userId => $transactions) {
            $transaction = $transactions->first();

            if ($this->option('detailed')) {
                $this->showMsg('Latest transactionID for #'. $userId .' is '.$transaction->transaction_id.'. Processing...');
            }

            /* Retrieve receipt Verification information */
            $this->verifyTransaction($transaction);
            sleep(2);
        }

        $this->showMsg('Finished');
    }

    /**
     * Show logs
     * @param GoogleSubscription $subscription [Subscription information]
     * @return boolean
     */
    private function verifyTransaction($subscription)
    {
        $user = User::find($subscription->user_id);

        if (!$user) {
            $this->showMsg('User #'.$subscription->user_id.' not found', 'error');
            return false;
        }

        $payload = [
            'productId'     => $subscription->package_id,
            'transactionId' => $subscription->transaction_id,
            'purchaseToken' => $subscription->purchase_token,
        ];


        $service = new GooglePaymentService;
        $service->setUser($user);
        $service->setProductId($subscription->package_id);
        $service->setTransactionId($subscription->transaction_id);
        $service->setPurchaseToken($subscription->purchase_token);

        /* Add latest receipt infromation and extend user PRO status if we missing latest receipt */
        try {
            $verifiedReceipt = $service->handleReceipt();
            /* Update user PRO status in case if paid access should be after current PRO status expiration date */
            if ($user->pro_expires_at < $verifiedReceipt->expires_at) {
                $this->showMsg('PRO Status updated for #'. $subscription->user_id.' '.$user->pro_expires_at.' => '.$verifiedReceipt->expires_at);
                $user->upgradeToPro($verifiedReceipt->expires_at, ProTypes::PAID);
                $service->createLogEntry($payload, GooglePostbacksLog::MANUALLY_UPDATED);
            }
        } catch (\Throwable $e) {
            $message  = $e->getMessage();
            $code     = $e->getCode();
            $payload += ['error_message' => $message, 'error_code' => $code];
            $service->createLogEntry($payload, GooglePostbacksLog::MANUAL_UPDATE_FAILED);
        }

        return true;
    }

    /**
     * Show logs
     * @param [string] $msg  [Content of message]
     * @param [string] $type [Type of message]
     * @return null
     */
    private function showMsg($msg, $type = null)
    {
        if ($this->option('debug')) {
            switch ($type) {
                case 'warning':
                    $this->warn(Carbon::now()->format('Y-m-d H:i:s').' - '. $this->infoMessageKey .' '.$msg);
                break;

                case 'error':
                    $this->error(Carbon::now()->format('Y-m-d H:i:s').' - '. $this->infoMessageKey .' '.$msg);
                break;

                default:
                    $this->info(Carbon::now()->format('Y-m-d H:i:s').' - '. $this->infoMessageKey .' '.$msg);
            }
        }
    }
}
