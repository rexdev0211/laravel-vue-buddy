<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class EventInvitation extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_REJECTED = 2;

    protected $table = 'event_invitations';

    protected $fillable = [
        'invited_by_user_id',
        'user_id',
        'event_id',
        'status',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event() {
        return $this->belongsTo('App\Event', 'event_id');
    }

    public function invitedByUser() {
        return $this->belongsTo('App\User', 'invited_by_user_id', 'id');
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
