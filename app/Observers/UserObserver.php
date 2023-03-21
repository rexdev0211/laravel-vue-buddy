<?php

namespace App\Observers;

use App\Services\SpamService;
use App\User;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Support\Facades\Redis;

class UserObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function created(User $user)
    {
        // WE DON'T GHOST THEM ANYMORE
        if (
            preg_match("/^[A-Z]{2}.*\d{2}[a-z]{4}@/", $user->email)
            ||
            preg_match("/[A-Z]{2}\d{2}[a-z]{4}@/", $user->email)
            &&
            preg_match("/^[A-Z]{2}.*\d{2}$/", $user->name)
            ||
            preg_match("/[A-Z]{2}\d{2}$/", $user->name)
            ||
            preg_match("/^{$user->name}[A-Z]{2}\d{2}[a-z]{4}/", $user->email)
            ||
            preg_match("/^[A-Z]{2}{$user->name}\d{2}[a-z]{4}/", $user->email)
        ) {
            $spamService = new SpamService();
            $spamService->setUser($user);
            $spamService->sendAdminEmail(SpamService::ACTION_SUSPEND, SpamService::REASON_SPAM_MAIL);
        }
    }

    /**
     * Handle the user "creating" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function creating(User $user)
    {
        $user->push_notifications = 'yes';
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        Redis::del('cached_user.' . $user->id);
        User::cleanAttributesCache($user->id);
    }

    /**
     * Handle the user "updating" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updating(User $user)
    {
        $lng = $user->lng >= User::MAX_ALLOWED_LONGITUDE_X || $user->lng <= User::MIN_ALLOWED_LONGITUDE_X ? 0 : $user->lng;
        $lat = $user->lat >= User::MAX_ALLOWED_LATITUDE_Y || $user->lat <= User::MIN_ALLOWED_LATITUDE_Y ? 0 : $user->lat;

        $lng = mb_convert_encoding($lng, 'UTF-8');
        $lat = mb_convert_encoding($lat, 'UTF-8');

        if (isset($user->location_type) && $user->location_type != 'automatic') {
            $user->gps_geom = \Helper::getGpsGeom($lng, $lat);
            $user->location = new Point($lat, $lng, 4326);	// (lat, lng)
        } else if (isset($user->location_type) && $user->location_type == 'automatic') {
            if (!request()->hasHeader('X-Login-As') && !request()->hasHeader('x-login-as')) {
                $user->gps_geom = \Helper::getGpsGeom($lng, $lat);
                $user->location = new Point($lat, $lng, 4326);	// (lat, lng)
            }
        }
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
