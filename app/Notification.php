<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

    protected $table = 'notifications';

    public $timestamps = false;

    protected $guarded = array('id', '_token');

    protected $dates = ['idate'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userFrom()
    {
        return $this->belongsTo('App\User', 'user_from');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userTo()
    {
        return $this->belongsTo('App\User', 'user_to');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function notificationEvent() {
        return $this->belongsTo('App\Event', 'event_id');
    }
}
