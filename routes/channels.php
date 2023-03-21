<?php

use App\Event;
use App\EventMembership;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('channel-{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('channel-event-membership', function () {
    return true;
});

Broadcast::channel('channel-group-chat-{id}', function ($user, $id) {
    $event = Event::find($id);
    if (!empty($event)) {
        $membership = EventMembership::search($user, $event);
        if (
            !empty($membership)
            &&
            in_array($membership->status, [
                EventMembership::STATUS_HOST,
                EventMembership::STATUS_MEMBER,
            ])
        ) {
            return true;
        }
    }
    return false;
});
