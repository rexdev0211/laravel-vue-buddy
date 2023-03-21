<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class OnesignalPlayer extends Model
{
    protected $table = 'onesignal_players';

    public $timestamps = false;

    protected $guarded = array('id', '_token');


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
