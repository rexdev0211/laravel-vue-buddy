<?php

namespace App\Observers;

use App\Models\Payment\AppleSubscription;
use Illuminate\Support\Facades\Redis;

class AppleObserver
{
    public function created(AppleSubscription $item)
    {
//        Redis::del('lastAppleTransaction.' . $item->user_id);
    }

    public function updated(AppleSubscription $item)
    {
//        Redis::del('lastAppleTransaction.' . $item->user_id);
    }

    public function deleted(AppleSubscription $item)
    {
//        Redis::del('lastAppleTransaction.' . $item->user_id);
    }
}