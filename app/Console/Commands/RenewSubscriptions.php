<?php

namespace App\Console\Commands;

use App\Services\Payments\GooglePaymentService;
use App\Services\PaymentService;
use App\User;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;

class RenewSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:renew-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew subscriptions';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        /*$request = [
            'productId' => 'buddy.pro.1m.abo',
            'transactionId' => 'GPA.3357-9268-1251-95494',
            'transactionDate' => '2021-01-25T12:11:41.794',
            'transactionReceipt' => [
                'orderId' => 'GPA.3357-9268-1251-95494',
                'packageName' => 'net.buddy',
                'productId' => 'buddy.pro.1m.abo',
                'purchaseTime' => 1611573101794,
                'purchaseState' => 0,
                'purchaseToken' => 'pdhijnennljpipgjdfomkeho.AO-J1Oz5pR5VJkizl1ZIMwptJfU0ow68LF9mApeN8oOVG3Ldfm2cEcuUA_57ikxX_L-q33GIJyNrO7YxmBA2jAb3bqJqEmqCOw',
                'autoRenewing' => true,
                'acknowledged' => false
            ],
            'purchaseToken' => 'pdhijnennljpipgjdfomkeho.AO-J1Oz5pR5VJkizl1ZIMwptJfU0ow68LF9mApeN8oOVG3Ldfm2cEcuUA_57ikxX_L-q33GIJyNrO7YxmBA2jAb3bqJqEmqCOw',
            'orderId' => 'GPA.3357-9268-1251-95494'
        ];

        $service = new GooglePaymentService();
        $service->setProductId($request['productId']);
        $service->setTransactionId($request['transactionId']);
        $service->setPurchaseToken($request['purchaseToken']);
        $service->setUser(User::find(103633));
        $service->handleReceipt();

        return 0;*/

        // Retrieve users
        $users = User::whereBetween('pro_expires_at', [
            Carbon::now()->toDateString(),
            Carbon::now()->addHours(6)->toDateString()
        ])->get()->all();

        // Try to renew their subscriptions
        $service = new PaymentService;
        foreach ($users as $user) {
            /** @var User $user */
            $service->setUser($user);
            try {
                $message = null;
                $subscriptionRenewed = $service->renewSubscription();
                if ($subscriptionRenewed) {
                    $message = "{$user->name} subscription renewed successfully!";
                } else {
                    $message = "{$user->name} subscription wasn't renewed";
                }

                $this->info($message);
                Log::info('Subscription succeed', [
                    'user' => $user,
                    'message' => $message
                ]);
            } catch (\Throwable $e) {
                $this->error("{$user->name} $e");
                Log::error('Subscription failed', [
                    'user' => $user,
                    'subscription' => $user->lastSubscription(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
