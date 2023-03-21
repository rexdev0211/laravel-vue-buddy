<?php

namespace App\Observers;

use App\Models\Payment\TwokchargeTransactions;
use Illuminate\Support\Facades\Redis;

class TwokObserver
{
    public function created(TwokchargeTransactions $item)
    {
//        Redis::del('lastTwokTransaction.' . $item->user_id);
    }

    public function updated(TwokchargeTransactions $item)
    {
//        Redis::del('lastTwokTransaction.' . $item->user_id);
    }

    public function deleted(TwokchargeTransactions $item)
    {
//        Redis::del('lastTwokTransaction.' . $item->user_id);
    }
}