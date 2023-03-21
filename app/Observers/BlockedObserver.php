<?php

namespace App\Observers;

use App\UserBlocked;
use Illuminate\Support\Facades\Redis;

class BlockedObserver
{
    public function updated(UserBlocked $blocked)
    {
        Redis::del('blocked_count:' . $blocked->user_id);
        Redis::del('blocked_count:' . $blocked->user_blocked_id);
    }
}