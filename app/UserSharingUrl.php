<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSharingUrl extends Model
{
    /**
     * @var array[]
     */
    protected $fillable = [
        'user_id', 'url_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sharingUrl() {
        return $this->belongsTo(SharingUrl::class, 'url_id');
    }
}
