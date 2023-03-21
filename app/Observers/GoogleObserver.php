<?php

namespace App\Observers;

use App\Models\Payment\GoogleSubscription;
use Illuminate\Support\Facades\Redis;

class GoogleObserver
{
    public function created(GoogleSubscription $item)
    {
//        Redis::del('lastGoogleTransaction.' . $item->user_id);
    }

    public function updated(GoogleSubscription $item)
    {
//        Redis::del('lastGoogleTransaction.' . $item->user_id);
    }

    public function deleted(GoogleSubscription $item)
    {
//        Redis::del('lastGoogleTransaction.' . $item->user_id);
    }
}