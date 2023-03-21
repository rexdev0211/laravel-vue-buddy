<?php

namespace App\Observers;

use App\Event;
use App\EventMembership;
use Illuminate\Support\Facades\Redis;

class EventMembershipObserver
{
    public function created(EventMembership $item)
    {
        $this->cleanCache($item);
    }

    public function updated(EventMembership $item)
    {
        $this->cleanCache($item);
    }

    public function deleted(EventMembership $item)
    {
        $this->cleanCache($item);
    }

    protected function cleanCache(EventMembership $item)
    {
        Event::cleanAttributesCache($item->id);
        
        Redis::del('events_members_with_ghost.'.$item->event_id); // TODO: update it
        Redis::del('events_members.'.$item->event_id); // TODO: update it
    }
}