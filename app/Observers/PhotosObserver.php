<?php

namespace App\Observers;

use App\Models\Payment\GoogleSubscription;
use App\User;
use App\UserPhoto;
use Illuminate\Support\Facades\Redis;

class PhotosObserver
{
    public function created(UserPhoto $item)
    {
        $this->cleanCache($item);
    }

    public function updated(UserPhoto $item)
    {
        $this->cleanCache($item);
    }

    public function deleted(UserPhoto $item)
    {
        $this->cleanCache($item);
    }
    
    protected function cleanCache(UserPhoto $item)
    {
        User::cleanAttributesCache($item->user_id);
    }
}