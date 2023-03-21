<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class EventLike extends Model {

    protected $table = 'event_likes';

    protected $guarded = array('id', '_token');

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event() {
        return $this->belongsTo('App\Event');
    }

    public function userActive() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

}
