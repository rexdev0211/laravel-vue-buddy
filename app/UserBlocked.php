<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserBlocked extends Model
{

    protected $table = 'user_blocked_map';

    public $timestamps = false;

    protected $guarded = array('id', '_token');

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function blocker() {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function blocked() {
        return $this->belongsTo('App\User', 'user_blocked_id');
    }

    /**
     * @param $firstUserId
     * @param $secondUserId
     * @return mixed
     */
    public static function isBlocked($firstUserId, $secondUserId) {
        return self::where('user_id', $firstUserId)
            ->where('user_blocked_id', $secondUserId)
            ->orWhere(function($query) use ($firstUserId, $secondUserId) {
                $query->where('user_id', $secondUserId)
                    ->where('user_blocked_id', $firstUserId);
            })
            ->exists();
    }
}
