<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFavorite extends Model
{

    protected $table = 'user_favorites_map';

    public $timestamps = false;

    protected $guarded = array('id', '_token');

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function favoriter() {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function favorited() {
        return $this->belongsTo('App\User', 'user_favorite_id');
    }

    /**
     * @param $askingUserId
     * @param $favoritedUserId
     * @return mixed
     */
    public static function isFavorite($askingUserId, $favoritedUserId) {
        return self::where('user_id', $askingUserId)
            ->where('user_favorite_id', $favoritedUserId)
            ->exists();
    }
}
