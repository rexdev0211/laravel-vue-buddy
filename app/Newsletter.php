<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{

    protected $table = 'newsletter';

    public $timestamps = false;

    protected $guarded = array('id', '_token');

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo('App\User');
    }
}
