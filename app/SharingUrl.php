<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SharingUrl extends Model
{
    /**
     * @var array[]
     */
    protected $fillable = [
        'url',
        'status',
        'expire_at',
        'views_limit',
    ];

    const SHARING_STATUS_ACTIVE = 'active',
          SHARING_STATUS_DISABLED = 'disabled';

    public function videos()
    {
        $this->belongsToMany(UserVideo::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sharingVideos()
    {
        return $this->belongsToMany(UserVideo::class, 'sharing_urls_videos', 'url_id', 'video_id');
    }
}
