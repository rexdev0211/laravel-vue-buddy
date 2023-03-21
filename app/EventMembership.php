<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class EventMembership extends Model
{
    const
        STATUS_HOST = 'host',
        STATUS_REQUESTED = 'requested',
        STATUS_REJECTED = 'rejected',
        STATUS_MEMBER = 'member',
        STATUS_LEAVED = 'leaved',
        STATUS_REMOVED = 'removed',

        ACTION_REQUEST = 'request',
        ACTION_ACCEPT = 'accept',
        ACTION_HOST_ACCEPT = 'hostAccept',
        ACTION_REJECT = 'reject',
        ACTION_LEAVE = 'leave',
        ACTION_REMOVE = 'remove',
        ACTION_REMOVE_EVENT = 'remove_event';

    protected $table = 'event_members_map';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $fillable = ['event_id', 'user_id', 'status'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event() {
        return $this->belongsTo('App\Event', 'event_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * @param User $user
     * @param Event $event
     *
     * @return null|EventMembership
     */
    public static function search(User $user, Event $event): ?EventMembership
    {
        return self::where([
            'event_id' => $event->id,
            'user_id'  => $user->id,
        ])->first();
    }

    /**
     * @return null|EventMembership
     */
    public static function searchByIds($userId, $eventId): ?EventMembership
    {
        return self::where([
            'event_id' => $eventId,
            'user_id'  => $userId,
        ])->first();
    }

    /**
     * @param User $user
     * @param Event $event
     *
     * @return bool
     */
    public static function isActiveMember(User $user, Event $event): bool
    {
        $membership = EventMembership::search($user, $event);
        return
            !empty($membership)
            &&
            in_array($membership->status, [EventMembership::STATUS_HOST, EventMembership::STATUS_MEMBER]);
    }
}
