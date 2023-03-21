<?php namespace App;

use Illuminate\Support\Facades\Redis;
use Jenssegers\Mongodb\Eloquent\Model;

class Message extends Model
{
    const
        CHANNEL_USER = 'user',
        CHANNEL_EVENT = 'event',
        CHANNEL_GROUP = 'group',

        TYPE_TEXT = 'text',
        TYPE_IMAGE = 'image',
        TYPE_LOCATION = 'location',
        TYPE_VIDEO = 'video',
        TYPE_LEFT = 'left',
        TYPE_JOINED = 'joined',

        FIELDS = [
            'user_from',
            'user_to',
            'event_id',
            'message',
            'msg_type',
            'image_id',
            'video_id',
            'channel',
            'is_read',
            'is_read_cloak',
            'is_sender_suspended',
            'is_bulk',
            'idate',
            'hash',
            'cancelled',
        ];

    protected $connection = 'mongodb';

    protected $collection = 'messages';

    protected $primaryKey = '_id';

    public $timestamps = false;

    protected $guarded = ['_id'];

    protected $dates = ['idate'];

    public function userFrom()
    {
        return $this->belongsTo('App\User', 'user_from');
    }

    public function userTo()
    {
        return $this->belongsTo('App\User', 'user_to');
    }

    public function event() {
        return $this->belongsTo('App\Event');
    }

    public function messageEvent()
    {
        return $this->belongsTo('App\Event', 'event_id', 'id');
    }

    public function groupMessagesRead()
    {
        return $this->belongsTo('App\Models\Event\EventMessagesRead', 'user_id', 'user_id');
    }

    public function image() {
        return $this->belongsTo('App\UserPhoto', 'image_id');
    }

    public function video() {
        return $this->belongsTo('App\UserVideo', 'video_id');
    }

    public function getGeneralAttributes(?User $user = null): array
    {
        $attributes = [
            'id' => $this->_id,
            'user_from' => $this->user_from,
            'user_to' => $this->user_to,
            'event_id' => $this->event_id,
            'msg_type' => $this->msg_type,
            'message' => $this->message,
            'image_id' => $this->image_id,
            'video_id' => $this->video_id,
            'channel' => $this->channel,
            'is_read' => $this->is_read,
            'idate' => $this->idate,
            'hash' => $this->hash ?? null,
            'cancelled' => (bool)($this->cancelled || $this->is_removed_by_sender),
        ];

        if ($this->channel == self::CHANNEL_GROUP && $this->user_from) {
//            $userFromAttributes = json_decode(Redis::get('userFromAttributes.' . $this->user_from) ?? '', true);

//            if (empty($userFromAttributes)) {
//                $userFromAttributes = $this->userFrom->getAttributesByMode(User::ATTRIBUTES_MODE_GROUP_MESSAGE, $user);
//                Redis::set('userFromAttributes.' . $this->user_from, json_encode($userFromAttributes ?? []));
//            }

            $attributes['user'] = $this->userFrom->getAttributesByMode(User::ATTRIBUTES_MODE_GROUP_MESSAGE, $user);
        }

        if (!empty($this->video)) {
            /** @var UserVideo $video */
            $video = $this->video;
            if ($video->thumbnail_type === 'gif') {
                $attributes['thumbnail_gif'] = $video->getGifThumbnailUrl();
            } else {
                $attributes['thumbnail_img'] = $video->getThumbnailUrl('180x180');
            }
            $video->setUrls(true, $user);
            $attributes['video_url'] = $video->video_url;
        }

        if (!empty($this->image)) {
            /** @var UserPhoto $image */
            $image = $this->image;
            $image->setUrls(true, $user);
            $attributes['photo_orig'] = $image->photo_orig;
            $attributes['photo_small'] = $image->photo_small;
        }

        if ($this->msg_type === 'location') {
            $attributes['message'] = str_replace(
                ['zoom:', 'lat:', 'lng:'],
                ['"zoom":','"lat":','"lng":'],
                $this->message
            );
        }

        return $attributes;
    }
}
