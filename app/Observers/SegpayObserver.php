<?php

namespace App\Observers;

use App\Models\Payment\SegpayPurchase;
use Illuminate\Support\Facades\Redis;

class SegpayObserver
{
    public function created(SegpayPurchase $item)
    {
//        Redis::del('lastSegpayTransaction.' . $item->user_id);
    }

    public function updated(SegpayPurchase $item)
    {
//        Redis::del('lastSegpayTransaction.' . $item->user_id);
    }

    public function deleted(SegpayPurchase $item)
    {
//        Redis::del('lastSegpayTransaction.' . $item->user_id);
    }
}