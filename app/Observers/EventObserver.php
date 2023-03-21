<?php

namespace App\Observers;

use App\Event;
use App\User;
use App\UserFavorite;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Support\Facades\Redis;

class EventObserver
{
    public function updating(Event $event)
    {
        $lng = $event->lng >= User::MAX_ALLOWED_LONGITUDE_X || $event->lng <= User::MIN_ALLOWED_LONGITUDE_X ? 0 : $event->lng;
        $lat = $event->lat >= User::MAX_ALLOWED_LATITUDE_Y || $event->lat <= User::MIN_ALLOWED_LATITUDE_Y ? 0 : $event->lat;

        $lng = mb_convert_encoding($lng, 'UTF-8');
        $lat = mb_convert_encoding($lat, 'UTF-8');

        $event->gps_geom = \Helper::getGpsGeom($lng, $lat);
        $event->location_geom = new Point($lat, $lng, 4326); // (lat, lng)
    }

    public function updated(Event $item)
    {
        $this->cleanCache($item);
    }

    public function deleted(Event $item)
    {
        $this->cleanCache($item);
    }

    protected function cleanCache(Event $item)
    {
        Event::cleanAttributesCache($item->id);

        Redis::del('cached_event.' . $item->id); // TODO: update
        Redis::del('events_members_with_ghost.'.$item->id); // TODO: update
        Redis::del('events_members.'.$item->id); // TODO: update
    }
}