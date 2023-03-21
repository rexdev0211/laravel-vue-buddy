<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserReported extends Model
{

    protected $table = 'user_reported_map';

    public $timestamps = false;

    protected $guarded = array('id', '_token');

    /**
     * Get reporter user.
     */
    public function reporter()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Get reported user.
     */
    public function reported()
    {
        return $this->hasOne(User::class, 'id', 'user_reported_id');
    }

}
