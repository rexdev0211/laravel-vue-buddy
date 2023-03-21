<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SharingUrlsView extends Model
{
    /**
     * @var array[]
     */
    protected $fillable = [
        'url_id',
        'ip_address',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sharingUrl() {
        return $this->belongsTo(SharingUrl::class, 'url_id');
    }
}
