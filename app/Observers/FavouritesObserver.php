<?php

namespace App\Observers;

use App\UserFavorite;
use Illuminate\Support\Facades\Redis;

class FavouritesObserver
{
    public function updated(UserFavorite $favorite)
    {
        Redis::del('favourites_count.' . $favorite->user_id);
        Redis::del('favourites_count.' . $favorite->user_favorite_id);
    }
}