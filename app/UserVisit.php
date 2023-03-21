<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserVisit extends Model
{

    protected $table = 'user_visits_map';

    public $timestamps = false;

    protected $guarded = array('id', '_token');

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visitor()
    {
        return $this->belongsTo('App\User', 'visitor_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visited()
    {
        return $this->belongsTo('App\User', 'visited_id');
    }
}
