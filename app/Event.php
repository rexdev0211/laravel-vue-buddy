<?php

namespace App;

use App\Message;
use App\Models\Event\EventMessagesRead;
use App\Facades\Helper;
use App\Models\Event\EventReport;
use App\Traits\SpatialTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use App\Traits\HybridRelations;
use App\Services\Timer;

class Event extends Model
{
    use HybridRelations;
    use SpatialTrait;

    protected $connection = 'mysql';

    public const
        ATTRIBUTES_MODE_FULL = 'full',
        ATTRIBUTES_MODE_GENERAL = 'general',
        ATTRIBUTES_MODE_DISCOVER = 'discover',
        ATTRIBUTES_MODE_LIST = 'list',

        STATUS_ACTIVE = 'active',
        STATUS_PENDING = 'pending',
        STATUS_DECLINED = 'declined',
        STATUS_SUSPENDED = 'suspended',
        STATUS_APPROVED = 'approved',


        GUIDE_FIELDS = [
            'title',
            'time',
            'event_date',
            'type',
            'address_type',
            'description',
            'location',
            'lat',
            'lng',
            'address',
            'locality',
            'state',
            'country',
            'country_code',
            'preview_photo',
            'photos',
            'videos',
            'deletedPhotos',
            'deletedVideos',
            'venue',
            'website',
            'name',
            'contact',
            'note',
            'featured',
            'status',
            'is_profile_linked'
        ],
        EVENT_FIELDS = [
            'title',
            'time',
            'event_date',
            'type',
            'chemsfriendly',
            'address_type',
            'description',
            'location',
            'lat',
            'lng',
            'address',
            'locality',
            'state',
            'is_profile_linked',
            'country',
            'country_code',
            'preview_photo',
            'photos',
            'videos',
            'tags',
            'deletedPhotos',
            'deletedVideos'
        ],
        BANG_FIELDS = [
            'title',
            'time',
            'event_date',
            'type',
            'address_type',
            'location',
            'lat',
            'lng',
            'address',
            'locality',
            'chemsfriendly',
            'state',
            'country',
            'country_code',
            'preview_photo',
            'deletedPhotos',
            'is_private',
        ],
        CLUB_FIELDS = [
            'title',
            'description',
            'time',
            'event_date',
            'type',
            'address_type',
            'location',
            'lat',
            'lng',
            'address',
            'locality',
            'state',
            'country',
            'country_code',
            'preview_photo',
            'deletedPhotos',
            'is_private',
        ],
        GUIDE_FIELDS_RULES = [
            'title'                 => 'required|string|max:50',
            'time'                  => 'required|string|max:50',
            'event_date'            => 'required|date|after:yesterday',
            'type'                  => 'required|string|max:32|in:guide',
            'address_type'          => 'required|string|in:full_address,city_only',
            'address'               => 'required|string|max:255',

            'description'           => 'required|string|max:3000',
            'lat'                   => 'required|numeric',
            'lng'                   => 'required|numeric',

            'location'              => 'nullable|string|max:150',
            'locality'              => 'nullable|string|max:100',
            'state'                 => 'nullable|string|max:100',
            'country'               => 'nullable|string|max:100',
            'country_code'          => 'nullable|string|max:9',
            'preview_photo'         => 'nullable|array',
            'photos'                => 'nullable|array',
            'videos'                => 'nullable|array',

            'name'                  => 'nullable|string|max:30',
            'website'               => 'nullable|string|max:750',
            'venue'                 => 'required|string',
            'contact'               => 'nullable|string|max:30',
            'note'                  => 'nullable|string|max:300'
        ],
        EVENT_FIELDS_RULES = [
            'title'                 => 'required|string|max:50',
            'time'                  => 'required|string|max:50',
            'event_date'            => 'required|date|after:yesterday',
            'type'                  => 'required|string|max:32|in:friends,fun',
            'address_type'          => 'required|string|in:full_address,city_only',
            'address'               => 'required|string|max:255',

            'chemsfriendly'         => 'required|boolean',
            'description'           => 'required|string|max:1000',
            'lat'                   => 'required|numeric',
            'lng'                   => 'required|numeric',
            'is_profile_linked'     => 'required|boolean',

            'location'              => 'nullable|string|max:150',
            'locality'              => 'nullable|string|max:100',
            'state'                 => 'nullable|string|max:100',
            'country'               => 'nullable|string|max:100',
            'country_code'          => 'nullable|string|max:9',
            'preview_photo'         => 'nullable|array',
            'photos'                => 'nullable|array',
            'videos'                => 'nullable|array',
            'tags'                  => 'nullable|array'
        ],
        BANG_FIELDS_RULES = [
            'title'                 => 'nullable|string|max:50',
            'time'                  => 'required|string|max:50',
            'event_date'            => 'required|date|after:yesterday',
            'type'                  => 'required|string|max:32|in:bang',
            'address_type'          => 'required|string|in:full_address,city_only',
            'address'               => 'required|string|max:255',

            'location'              => 'nullable|string|max:150',
            'locality'              => 'nullable|string|max:100',
            'state'                 => 'nullable|string|max:100',
            'country'               => 'nullable|string|max:100',
            'country_code'          => 'nullable|string|max:9',
            'lat'                   => 'numeric',
            'lng'                   => 'numeric',
            'preview_photo'         => 'nullable|array',
            'is_private'            => 'nullable|boolean',
        ],
        CLUB_FIELDS_RULES = [
            'title'                 => 'required|string|max:50',
            'description'           => 'required|string|max:500',
            'address'               => 'required|string|max:255',

            'location'              => 'nullable|string|max:150',
            'locality'              => 'nullable|string|max:100',
            'state'                 => 'nullable|string|max:100',
            'country'               => 'nullable|string|max:100',
            'country_code'          => 'nullable|string|max:9',
            'lat'                   => 'numeric',
            'lng'                   => 'numeric',
            'preview_photo'         => 'nullable|array',
            'website'               => 'nullable|string|max:750',
            'is_private'            => 'nullable|boolean',
        ],

        TYPE_GUIDE = 'guide',
        TYPE_FRIENDS = 'friends',
        TYPE_FUN = 'fun',
        TYPE_FUN_CHEMS_FRIENDLY = 'fun-cf',
        TYPE_BANG = 'bang',
        TYPE_CLUB = 'club';

    protected $table = 'events';

    protected $guarded = ['id', '_token'];

    protected $hidden = [
        'gps_geom',
        'location_geom',
    ];

    protected $spatialFields = [
        'location_geom',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function photos()
    {
        return $this->belongsToMany('App\UserPhoto')->withPivot('is_default');
    }

    public function videos()
    {
        return $this->belongsToMany('App\UserVideo');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function allMembers()
    {
        return $this
            ->belongsToMany('App\User', 'event_members_map', 'event_id', 'user_id')
            ->orderByRaw('event_members_map.status = "' . EventMembership::STATUS_HOST . '" desc')
            ->where('users.status', '!=', User::STATUS_GHOSTED)
            ->orderBy('id', 'desc')
            ->withPivot('status');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function members()
    {
        return $this
            ->belongsToMany('App\User', 'event_members_map', 'event_id', 'user_id')
            ->whereNotIn('event_members_map.status', [
                EventMembership::STATUS_LEAVED,
                EventMembership::STATUS_REMOVED,
                EventMembership::STATUS_REJECTED,
            ])
            ->where('users.status', '!=', User::STATUS_GHOSTED)
            ->orderByRaw('event_members_map.status = "' . EventMembership::STATUS_HOST . '" desc')
            ->orderBy('id', 'desc')
            ->withPivot('status');
    }

    /**
     * @param User $retriever
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activeMembersIncludingRetriever(User $retriever)
    {
        return $this
            ->allMembers()
            ->where('users.status', '!=', User::STATUS_GHOSTED)
            ->where(function ($query) use ($retriever) {
                $query
                    ->whereIn('event_members_map.status', [
                        EventMembership::STATUS_HOST,
                        EventMembership::STATUS_MEMBER,
                    ])
                    ->orWhere(function ($query) use ($retriever) {
                        $query
                            ->where(['event_members_map.user_id' => $retriever->id])
                            ->whereIn('event_members_map.status', [
                                EventMembership::STATUS_LEAVED,
                                EventMembership::STATUS_REMOVED,
                                EventMembership::STATUS_REJECTED
                            ]);
                    });
            });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activeMembers()
    {
        return $this->members()
            ->whereIn('event_members_map.status', [
                EventMembership::STATUS_HOST,
                EventMembership::STATUS_MEMBER,
            ]);
    }

    /**
     * @param $retriever
     * @return BelongsToMany
     */
    public function activeMembersWithGhost($retriever): BelongsToMany
    {
        $query = $this->members()->whereIn('event_members_map.status', [
            EventMembership::STATUS_HOST,
            EventMembership::STATUS_MEMBER,
        ])->orderBy('users.id');

        if (!is_null($retriever)) {
            $query->orWhere(function ($query) use ($retriever) {
                $query->where('event_members_map.user_id', $retriever->id)
                    ->where('event_members_map.event_id', $this->id)
                    ->where('event_members_map.status', EventMembership::STATUS_MEMBER);
            });
        }

        return $query;
    }

    /**
     * @param $user
     * @return int
     */
    public function activeMembersWithGhostCountAttribute($user): int
    {
        Timer::start('event-active-members-with-ghost-count-attribute:' . ($user->id ?? 0)  . ':' . $this->id);
        $count = $this->activeMembersWithGhost($user)->get()->unique()->count();
        Timer::end('event-active-members-with-ghost-count-attribute:' . ($user->id ?? 0) . ':' . $this->id);

        return $count;
    }

    public function getActiveMembersCountAttribute(): int
    {
        return $this->activeMembers()->count();
    }

    public function getMembersList(?User $retriever = null)
    {
        Timer::start('event-get-members-list:' . ($retriever->id ?? 'unknown') . ':' . $this->id);

        $isHost = false;
        $ghostMember = [];

        if (
            !empty($retriever)
            &&
            ($retriever->id ?? null) == $this->user_id
        ) {
            $isHost = true;
        }

        if ($isHost) {
            $members = $this->members()->get()->unique();
        } elseif (!empty($retriever)) {
            $members = Helper::isApp()
                           ? $this->activeMembersIncludingRetriever($retriever)->get()
                           : $this->activeMembersWithGhost($retriever)->get()->unique();
        } else {
            $members = $this->activeMembersWithGhost($this->user)->get()->unique();
        }

        $data = $members->map(function ($user) use ($retriever, $isHost) {
            $data = User::getCachedAttributesByMode($user, User::ATTRIBUTES_MODE_GENERAL, $retriever);
            $data['status'] = $user->pivot->status;
            $data['blocked'] = $isHost ? $user->isBlockedBy($retriever) : false;

            return $data;
        });

        Timer::end('event-get-members-list:' . ($retriever->id ?? 'unknown') . ':' . $this->id);

        return $data;
    }

    /**
     * Get unread group messages by retriever counter for current event group chat
     * @param  User $retriever
     * @return integer
     */
    public function getUnreadGroupMessagesCounter(?User $retriever = null)
    {
        Timer::start('event-get-unread-group-messages-counter:' . ($retriever->id ?? 'unknown') . ':' . $this->id);
        $data = null;
        if ($retriever) {
            $latestRead = EventMessagesRead::where('user_id', $retriever->id)
                                           ->where('event_id', $this->id)
                                           ->first();

            $count = Message::where('channel', 'group')
                            ->where('event_id', $this->id)
                            ->where('user_from', '<>', $retriever->id)
                            ->whereNull('cancelled');

            if ($latestRead) {
                $count = $count->where('idate', '>', $latestRead->latest_read);
            }

            $data = $count->count();
        }
        Timer::end('event-get-unread-group-messages-counter:' . ($retriever->id ?? 'unknown') . ':' . $this->id);

        return $data;
    }

    /**
     * Get unread group messages by retriever counter for current event group chat
     * @param  User $retriever
     * @return integer
     */
    public function getUnreadMessagesCounter(?User $retriever = null)
    {
        Timer::start('event-get-unread-messages-counter:' . ($retriever->id ?? 'unknown') . ':' . $this->id);
        $data = null;
        if ($retriever) {
            $data = Message::where('channel', Message::CHANNEL_EVENT)
                           ->where('event_id', $this->id)
                           ->where('user_to', '=', $retriever->id)
                           ->where('is_read', '!=', 'yes')
                           ->count();
        }
        Timer::end('event-get-unread-messages-counter:' . ($retriever->id ?? 'unknown') . ':' . $this->id);

        return $data;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('App\Tag', 'event_tags_map');
    }

    /**
     * Get the likes for the event.
     */
    public function likes()
    {
        return $this->hasMany('App\EventLike', 'event_id', 'id');
    }

    /**
     * Get authorized user like associated with the event.
     */
    public function myLike(?User $user)
    {
        Timer::start('event-my-like:' . ($user->id ?? 'unknown') . ':' . $this->id);
        $result = false;
        if ($user) {
            $result = $this->hasOne('App\EventLike', 'event_id', 'id')->where('user_id', $user->id)->exists();
        }
        Timer::end('event-my-like:' . ($user->id ?? 'unknown') . ':' . $this->id);

        return $result;
    }

    /**
     * @return bool
     */
    public function isSuspended()
    {
        return $this->status == 'suspended';
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return !$this->isSuspended();
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return (bool) $this->is_private == 1;
    }

    /**
     * @return string
     */
    public function getTypeCaptionAttribute()
    {
        return
            trans("string.{$this->type}")
            .
            ($this->type == self::TYPE_FUN && $this->chemsfriendly ? ' (cf)' : '');
    }

    /**
     * Report Event
     * @param integer $userId
     * @param string $reason
     *
     * @return bool
     */
    public function reportByUser($userId, $reason): bool
    {
        EventReport::firstOrCreate([
            'user_id'  => $userId,
            'event_id' => $this->id,
            'reason'   => $reason,
        ]);

        return true;
    }

    public function getDefaultPhoto(): ?UserPhoto
    {
        return $this->photos->first(function ($value) {
            return $value->pivot->is_default == 'yes';
        });
    }

    /**
     * @param string $size
     * @param bool $ignoreRestrictions
     * @param User|null $recipient
     * @param User|null $currentUser
     * @param bool $isDiscover
     * @return string|null
     */
    public function getPhotoUrl(string $size = 'orig', bool $ignoreRestrictions = false, ?User $recipient = null, ?User $currentUser = null, bool $isDiscover = false): ?string
    {
        if ($recipient) {
            $user = $recipient;
        } else {
            $user = $currentUser ?? request()->user();
        }
        $photoRating = $this->getPhotoRating();

        $showUserAdult = ($user->isPro() && $user->view_sensitive_media === 'no') || !$user->isPro();

        if (
            !$ignoreRestrictions // Personal images are always visible
            &&
            Helper::isApp()
            &&
            $photoRating && $photoRating !== UserPhoto::RATING_CLEAR
            &&
            $showUserAdult
        ) {
            return UserPhoto::IMAGE_PATH_ADULT. '_180x180.jpg';
        }

        $defaultPhoto = $this->getDefaultPhoto();
        return !empty($defaultPhoto) ?
            $defaultPhoto->getUrl($size, $ignoreRestrictions, $recipient)
            :
            UserPhoto::DEFAULT_IMAGE_SMALL;
    }

    public function getPhotoRating(): ?string
    {
        Timer::start('event-get-photo-rating:' . $this->id);
        $defaultPhoto = $this->getDefaultPhoto();
        $photo = !empty($defaultPhoto) ? $defaultPhoto->getRating() : null;
        Timer::end('event-get-photo-rating:' . $this->id);

        return $photo;
    }

    /**
     * @param User|null $retriever
     *
     * @return array
     */
    public function getAllAttributes(?User $retriever): array
    {
        return $this->getAttributesByMode(self::ATTRIBUTES_MODE_FULL, $retriever);
    }

    /**
     * @param string $mode
     * @param User|null $retriever
     *
     * @return array
     */
    public function getAttributesByMode(string $mode, ?User $retriever = null): array
    {
        Timer::start('event-get-arrtibutes-by-mode:' . $mode . ':' . $this->id);
        if (defined("FORCE_CACHE_FILLING") || (bool) config('cache.users_cache_attributes', false) === true) {
            $data = json_decode(Redis::get('event_attributes_by_mode.' . $this->id.'.'.$mode) ?? '', true);
        }

        if (empty($data)) {
            if (
                empty($retriever)
                &&
                $mode !== self::ATTRIBUTES_MODE_GENERAL
            ) {
                throw new \Error('Retriever attribute shouldn`t be empty in mode=ATTRIBUTES_MODE_GENERAL');
            }

            $data = [];

            if ($mode == self::ATTRIBUTES_MODE_GENERAL) {
                $data = [
                    'id' => $this->id,
                    'title' => $this->title,
                    'photo_rating' => $this->getPhotoRating(),
                    'type' => $this->type,
                    'locality' => $this->locality,
                    'status' => $this->status
                ];
            }

            if ($mode == self::ATTRIBUTES_MODE_DISCOVER) {
                $data = [
                    'id' => $this->id,
                    'title' => $this->title,
                    'photo_rating' => $this->getPhotoRating(),
                    'type' => $this->type,

                    'time' => $this->time,
                    'hasVideos' => $this->videos->count(),
                    'likes' => $this->getLikesCount(),
                    'sticky' => $this->is_sticky,
                    'chemsfriendly' => $this->chemsfriendly,
                    'location' => $this->location,
                    'locality' => $this->locality,
                    'lat' => (float)$this->lat,
                    'lng' => (float)$this->lng,
                    'address_type' => $this->address_type,
                    'country' => $this->country,
                    'country_code' => $this->country_code,
                    'state' => $this->state,
                    'address' => $this->address,
                    'website' => $this->website,
                    'contact' => $this->contact,
                    'name' => $this->name,
                    'venue' => $this->venue,
                    'featured' => $this->featured,
                    'status' => $this->status
                ];
            }

            if ($mode == self::ATTRIBUTES_MODE_FULL) {
                $data = [
                    'id' => $this->id,
                    'title' => $this->title,
                    'time' => $this->time,
                    'type' => $this->type,
                    'chemsfriendly' => $this->chemsfriendly,

                    'location' => $this->location,
                    'locality' => $this->locality,
                    'lat' => (float)$this->lat,
                    'lng' => (float)$this->lng,
                    'address_type' => $this->address_type,
                    'country' => $this->country,
                    'country_code' => $this->country_code,
                    'state' => $this->state,
                    'address' => $this->address,
                    'likes' => $this->getLikesCount(),

                    'is_profile_linked' => (bool)$this->is_profile_linked,
                    'sticky' => (bool)$this->is_sticky,

                    'photo_rating' => $this->getPhotoRating(),

                    'note' => $this->note,
                    'website' => $this->website,
                    'contact' => $this->contact,
                    'name' => $this->name,
                    'venue' => $this->venue,
                    'featured' => $this->featured,
                    'status' => $this->status,
                ];
            }

            $data['description'] = $this->description;
            $data['tags'] = $this->tags;
            $data['user_id'] = $this->user_id;
            $data['date'] = $this->event_date;
            $data['event_date'] = $this->event_date;
            $data['is_private'] = $this->isPrivate();

            if (defined("FORCE_CACHE_FILLING") || (bool)config('cache.users_cache_attributes', false) === true) {
                Redis::set('event_attributes_by_mode.' . $this->id.'.'.$mode, json_encode($data ?? []));
            }
        }

        $isHost = $this->user_id == ($retriever->id ?? null);

        if ($retriever) {
            Timer::start('event-search-membership:' . $mode . ':' . $this->id);
            $membership = EventMembership::search($retriever, $this);
            Timer::end('event-search-membership:' . $mode . ':' . $this->id);
        }
        $membershipStatus = $membership->status ?? null;

        $data['photo_small']       = $this->getPhotoUrl('180x180', $isHost, null, $retriever);
        $data['photo_orig']        = $this->getPhotoUrl('orig', $isHost, null, $retriever);
        $data['isMine']            = $isHost;
        $data['isOnline']          = $this->user->isOnline();
        $data['wasRecentlyOnline'] = $this->user->wasRecentlyOnline();

        if ($mode == self::ATTRIBUTES_MODE_DISCOVER || $mode == self::ATTRIBUTES_MODE_FULL) {
            Timer::start('event-get-distance-in-meters:' . $mode . ':' . $this->id);
            $data['distanceMeters'] = $this->distanceMeters ?: \Backend::getDistanceMetersBetween($retriever->lat, $retriever->lng, $this->lat, $this->lng);
            Timer::end('event-get-distance-in-meters:' . $mode . ':' . $this->id);

            $data['isLiked'] = (bool) $this->myLike($retriever);
        }

        if ($mode == self::ATTRIBUTES_MODE_FULL) {
            Timer::start('event-get-photos-and-videos:' . $mode . ':' . $this->id);
            $photos = $this->photos->transform(function ($photo) use ($isHost, $retriever) {
                /** @var UserPhoto $photo */
                $photo->setUrls($isHost, $retriever);
                return $photo;
            });

            $videos = $this->videos->transform(function ($video) use ($isHost, $retriever) {
                /** @var UserVideo $video */
                $video->setUrls($isHost, $retriever);
                return $video;
            });

            $data['videos'] = $videos;
            $data['photos'] = $photos;
            $data['user']   = $this->is_profile_linked
                                  ? $this->user->getAttributesByMode(User::ATTRIBUTES_MODE_GENERAL, $retriever)
                                  : null;
            Timer::end('event-event-get-photos-and-videos:' . $mode . ':' . $this->id);
        }

        if (!empty($membershipStatus)) {
            $data['membership'] = $membershipStatus;
        }

        if ($this->type == self::TYPE_BANG || $this->type == self::TYPE_CLUB) {
            $data['members']        = $this->getMembersList($retriever);
            $data['unreadMessages'] = $this->getUnreadGroupMessagesCounter($retriever);
        } else {
            $data['unreadMessages'] = $this->getUnreadMessagesCounter($retriever);
        }

        $data['members_count'] = $this->activeMembersWithGhostCountAttribute($retriever);

        Timer::end('event-get-arrtibutes-by-mode:' . $mode . ':' . $this->id);

        return $data;
    }

    public function getAttributesByModeOld(string $mode, ?User $retriever = null): array
    {
        if (
            empty($retriever)
            &&
            $mode !== self::ATTRIBUTES_MODE_GENERAL
        ) {
            throw new \Error('Retriever attribute shouldn`t be empty in mode=ATTRIBUTES_MODE_GENERAL');
        }

        $isHost = $this->user_id == ($retriever->id ?? null);

        if (
            $this->type === self::TYPE_BANG
            &&
            !empty($retriever)
        ) {
            $membership = EventMembership::search($retriever, $this);
        }
        $membershipStatus = $membership->status ?? null;

        $data = [];
        if ($mode == self::ATTRIBUTES_MODE_GENERAL) {
            $data = [
                'id'                => $this->id,
                'title'             => $this->title,
                'photo_small'       => $this->getPhotoUrl('180x180', $isHost, null, $retriever),
                'photo_orig'        => $this->getPhotoUrl('orig', $isHost, null, $retriever),
                'photo_rating'      => $this->getPhotoRating(),
                'isMine'            => $isHost,
                'date'              => $this->event_date,
                'type'              => $this->type,
                'isOnline'          => $this->user->isOnline(),
                'wasRecentlyOnline' => $this->user->wasRecentlyOnline(),
                'locality'          => $this->locality,
                'status'            => $this->status
            ];
        }

        if ($mode == self::ATTRIBUTES_MODE_DISCOVER) {
            $data = [
                'id'                => $this->id,
                'title'             => $this->title,
                'photo_small'       => $this->getPhotoUrl('180x180', $isHost, null, $retriever),
                'photo_orig'        => $this->getPhotoUrl('orig', $isHost, null, $retriever),
                'photo_rating'      => $this->getPhotoRating(),
                'isMine'            => $isHost,
                'date'              => $this->event_date,
                'type'              => $this->type,
                'isOnline'          => $this->user->isOnline(),
                'wasRecentlyOnline' => $this->user->wasRecentlyOnline(),

                'time'              => $this->time,
                'hasVideos'         => $this->videos->count(),
                'likes'             => $this->getLikesCount(),
                'isLiked'           => (bool)$this->myLike($retriever),
                'sticky'            => $this->is_sticky,
                'chemsfriendly'     => $this->chemsfriendly,
                'location'          => $this->location,
                'locality'          => $this->locality,
                'lat'               => (float)$this->lat,
                'lng'               => (float)$this->lng,
                'address_type'      => $this->address_type,
                'country'           => $this->country,
                'country_code'      => $this->country_code,
                'state'             => $this->state,
                'address'           => $this->address,
                'distanceMeters'    => \Backend::getDistanceMetersBetween($retriever->lat, $retriever->lng, $this->lat, $this->lng),
                'website'           => $this->website,
                'contact'           => $this->contact,
                'name'              => $this->name,
                'venue'             => $this->venue,
                'featured'          => $this->featured,
                'status'            => $this->status
            ];
        }

        if ($mode == self::ATTRIBUTES_MODE_FULL) {
            $photos = $this->photos->transform(function ($photo) use ($isHost, $retriever) {
                /** @var UserPhoto $photo */
                $photo->setUrls($isHost, $retriever);
                return $photo;
            });

            $videos = $this->videos->transform(function ($video) use ($isHost, $retriever) {
                /** @var UserVideo $video */
                $video->setUrls($isHost, $retriever);
                return $video;
            });

            $membership = EventMembership::search($retriever, $this);
            $membershipStatus = $membership->status ?? null;

            $data = [
                'id'                => $this->id,
                'user_id'           => $this->user_id,
                'title'             => $this->title,
                'description'       => $this->description,
                'event_date'        => $this->event_date,
                'time'              => $this->time,
                'type'              => $this->type,
                'chemsfriendly'     => $this->chemsfriendly,

                'location'          => $this->location,
                'locality'          => $this->locality,
                'lat'               => (float)$this->lat,
                'lng'               => (float)$this->lng,
                'address_type'      => $this->address_type,
                'country'           => $this->country,
                'country_code'      => $this->country_code,
                'state'             => $this->state,
                'address'           => $this->address,
                'likes'             => $this->getLikesCount(),
                'tags'              => $this->tags,

                'is_profile_linked' => (bool)$this->is_profile_linked,
                'sticky'            => (bool)$this->is_sticky,
                'isLiked'           => (bool)$this->myLike($retriever),

                'videos'            => $videos,
                'photos'            => $photos,
                'photo_small'       => $this->getPhotoUrl('180x180', $isHost, null, $retriever),
                'photo_orig'        => $this->getPhotoUrl('orig', $isHost, null, $retriever),
                'photo_rating'      => $this->getPhotoRating(),

                'distanceMeters'    => \Backend::getDistanceMetersBetween($retriever->lat, $retriever->lng, $this->lat, $this->lng),
                'user'              => $this->is_profile_linked ?
                    $this->user->getAttributesByMode(User::ATTRIBUTES_MODE_GENERAL, $retriever)
                    :
                    null,
                'isOnline'          => $this->user->isOnline(),
                'wasRecentlyOnline' => $this->user->wasRecentlyOnline(),
                'note'              => $this->note,
                'website'           => $this->website,
                'contact'           => $this->contact,
                'name'              => $this->name,
                'venue'             => $this->venue,
                'featured'          => $this->featured,
                'status'            => $this->status,

                'is_private'        => $this->isPrivate(),
            ];
        }


        if ($this->type == self::TYPE_BANG) {
            $data['members'] = $this->getMembersList($retriever);
            $data['unreadMessages'] = $this->getUnreadGroupMessagesCounter($retriever);
        } else {
            $data['unreadMessages'] = $this->getUnreadMessagesCounter($retriever);
        }

        if (!empty($membershipStatus)) {
            $data['membership'] = $membershipStatus;
        }
        $data['members_count'] = $this->activeMembersWithGhostCountAttribute($retriever);

        return $data;
    }

    public static function getNonStrictValidationRules(string $type)
    {
        $rules = null;

        if ($type === self::TYPE_GUIDE) {
            $rules = self::GUIDE_FIELDS_RULES;
        } elseif ($type === self::TYPE_FUN || $type === self::TYPE_FRIENDS) {
            $rules = self::EVENT_FIELDS_RULES;
        } elseif ($type === self::TYPE_BANG) {
            $rules = self::BANG_FIELDS_RULES;
        }

        $rules = collect($rules)
            ->map(function ($rule, $fieldName) {
                if ($fieldName == 'type') {
                    return $rule;
                }
                return str_replace('required|', '', $rule);
            })
            ->toArray();

        return $rules;
    }

    /**
     * @return int
     */
    public function getLikesCount(): int
    {
        Timer::start('event-get-likes-count:' . $this->id);
        $data = array_key_exists('likes', $this->getRelations()) ? count($this->getRelation('likes')) : 0;
        Timer::end('event-get-likes-count:' . $this->id);

        return $data;
    }

    public static function getCachedEvent($eventId)
    {
        if (defined("FORCE_CACHE_FILLING") || (bool) config('cache.users_cache_attributes', false) === true) {
            $event = json_decode((string)Redis::get('cached_event.' . $eventId) ?? '');

            if (empty($event)) {
                $event = Event::where('id', $eventId)->first();
                Redis::set('cached_event.' . $eventId, json_encode($event));
            }

            return $event;
        }

        return Event::where('id', $eventId)->first();
    }

    public static function getCachedAttributesByMode($baseEvent, string $mode, ?User $retriever = null)
    {
        if (method_exists($baseEvent, 'getAttributesByMode')) {
            return $baseEvent->getAttributesByMode($mode, $retriever);
        }

        /** @var Event $fres$freshedEventhedUser */
        $freshedEvent = User::find($baseEvent->id);

        return $freshedEvent->getAttributesByMode($mode, $retriever);
    }

    public static function cleanAttributesCache($eventId)
    {
        Redis::del('event_attributes_by_mode.'.$eventId.'.'.Event::ATTRIBUTES_MODE_GENERAL);
        Redis::del('event_attributes_by_mode.'.$eventId.'.'.Event::ATTRIBUTES_MODE_FULL);
        Redis::del('event_attributes_by_mode.'.$eventId.'.'.Event::ATTRIBUTES_MODE_DISCOVER);
    }
}
