<?php namespace App\Repositories;

use DB;
use App\User;
use App\Event;
use Exception;

use App\UserPhoto;
use App\UserVideo;
use Carbon\Carbon;
use App\EventMembership;
use App\Services\SpamService;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class EventRepository extends BaseRepository
{
    public function __construct(?Event $model = null)
    {
        if (empty($model)) {
            $model = new Event();
        }
        parent::__construct($model);
    }

    /**
     * @param $id
     * @return Event
     */
    public function findEvent($id)
    {
        return $this->find($id);
    }

    /**
     * @param $id
     * @param $currentUser
     * @return array|mixed
     */
    public function findEventWithOwner($id, $currentUser)
    {
        $event = $this
            ->selectRaw("events.*, users.last_active, ST_Distance_sphere(ST_GeomFromText('point(".$currentUser->lat." ".$currentUser->lng.")', 4326), events.location_geom) AS distanceMeters,
                event_likes.user_id isLiked")
            ->join('users', 'users.id', 'events.user_id')
            ->leftJoin('event_likes', function ($join) use ($currentUser) {
                $join->on('event_likes.user_id', '=', DB::raw($currentUser->id))
                     ->on('event_likes.event_id', '=', 'events.id');
            })
            ->where('events.status', 'active')
            ->where('events.id', $id)
            ->with('tags')
            ->with('photos')
            ->with('videos')
            ->first();

        if ($event != null) {
            /** @var Event $event */
            $event = $event->getAllAttributes($currentUser);
        }

        return $event;
    }

    /**
     * @param $id
     * @return Event
     */
    public function findEventWithAttachments($id)
    {
        $event = $this
            ->with('photos')
            ->with('videos')
            ->whereId($id)
            ->first();

        $event->videos->transform(function (UserVideo $video) use ($event) {
            $video->setUrls(true);
            return $video;
        });

        return $event;
    }

    /**
     * @param $page
     * @param $perPage
     * @param $orderBy
     * @param $orderBySort
     * @return mixed
     */
    public function getEventsList($page, $perPage, $orderBy, $orderBySort, $title, $id, $status, $occur, $ownerName = null, $ownerId = null, $type = null)
    {
        if ($occur == 'future') {
            $orderBy = 'event_date';
            $orderBySort = 'asc';
        } else {
            $orderBy = 'event_date';
            $orderBySort = 'desc';
        }

        $startDate = Carbon::today();
        $nowHour = Carbon::now()->hour;
        if ($nowHour < 5) {
            $startDate->subDay();
        }

        $list = $this
            ->with('user')
            ->filterByLike('title', $title)
            ->filterBy('id', $id)
            ->where(function ($query) use ($occur, $startDate) {
                if ($occur == 'future') {
                    $query->where('event_date', '>=', $startDate);
                } elseif ($occur == 'past') {
                    $query->where('event_date', '<', $startDate);
                }
            });

        if ($status) {
            $list = $list->filterBy('status', $status);
        } else {
            $list = $list->whereIn('status', [Event::STATUS_APPROVED, Event::STATUS_ACTIVE, Event::STATUS_SUSPENDED]);
        }

        if ($type) {
            $list = $list->where('type', $type);
        }

        if ($ownerName) {
            $list = $list->whereHas('user', function ($query) use ($ownerName) {
                $query->where('name', 'LIKE', "%{$ownerName}%");
            });
        }

        if ($ownerId) {
            $list = $list->whereUserId($ownerId);
        }

        return $list
            ->orderBy($orderBy, $orderBySort)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @param $page
     * @param $perPage
     * @return mixed
     */
    public function getEventsSubmissions($page, $perPage)
    {
        return $this->with('user')
                     ->where('type', Event::TYPE_GUIDE)
                     ->whereIn('status', [Event::STATUS_PENDING, Event::STATUS_DECLINED])
                     ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createEvent(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param $eventId
     * @param $userId
     * @return mixed
     */
    public function deleteEvent($eventId, $userId)
    {
        return $this->where('id', $eventId)->where('user_id', $userId)->delete();
    }

    /**
     * @param $maxDate
     * @return mixed
     */
    public function deleteOldEvents($maxDate)
    {
        return $this->where('event_date', '<', $maxDate)->delete();
    }

    public function safeCreate(User $user, array $data)
    {
        $event = $this->persist($user, null, $data);
        return $event;
    }

    public function safeUpdate(User $user, Event $event, array $data)
    {
        $event = $this->persist($user, $event, $data);
        return $event;
    }

    public function persist(User $user, ?Event $event, array $data)
    {
        $tagRepository = new TagRepository();
        $userPhotoRepository = new PhotoRepository();

        $type = $data['type'];

        if ($type == Event::TYPE_BANG) {
            $collectOnly = Event::BANG_FIELDS;
        } else if ($type == Event::TYPE_GUIDE) {
            $collectOnly = Event::GUIDE_FIELDS;
        } else if ($type === Event::TYPE_FUN || $type === Event::TYPE_FRIENDS) {
            $collectOnly = Event::EVENT_FIELDS;
        } else if ($type == Event::TYPE_CLUB) {
            $collectOnly = Event::CLUB_FIELDS;
        } else {
            $collectOnly = Event::EVENT_FIELDS;
        }

        $data = collect($data)->only($collectOnly)->toArray();

        if ($event && !empty($data['deletedPhotos'])) {
            DB::table('event_user_photo')
                ->where('event_id', '=', $event->id)
                ->whereIn('user_photo_id', $data['deletedPhotos'])
                ->delete();
        }
        unset($data['deletedPhotos']);

        if ($event && !empty($data['deletedVideos'])) {
            DB::table('event_user_video')
                ->where('event_id', '=', $event->id)
                ->whereIn('user_video_id', $data['deletedVideos'])
                ->delete();
        }
        unset($data['deletedVideos']);

        // Verify if there any blocked type of content
        $title = $data['title'];
        $description = $data['description'] ?? '';

        $spamService = new SpamService;
        $spamService->setScope(SpamService::SCOPE_EVENT);
        $spamService->setUser($user);
        if (!empty($event)) {
            $spamService->setEvent($event);
        }
        $spamService->setContent("$title\n$description");
        $suspended = $spamService->userSuspendAttempt();

        if ($suspended) {
            throw new \Error('Such description is not allowed', 422);
        }

        if ($data['type'] === Event::TYPE_GUIDE) {
            $data['status'] = Event::STATUS_PENDING;
        }

        // Cast types
        if ($data['type'] !== Event::TYPE_BANG && $data['type'] !== Event::TYPE_CLUB) {
            $data['photos'] = array_filter($data['photos'] ?? []);
            $data['videos'] = array_filter($data['videos'] ?? []);
            $data['tags'] = array_filter($data['tags'] ?? []);

            if ($data['type'] === Event::TYPE_FUN || $data['type'] === Event::TYPE_FRIENDS) {
                $data['is_profile_linked'] = ((int) $data['is_profile_linked']) ? 1 : 0;
            } else {
                $data['is_profile_linked'] = 0;
            }

            foreach ($data['videos'] as $video) {
                /** @var UserVideo $v */
                $v = UserVideo::find($video);
                $r = new VideoRepository($v);
                $r->setIncludedInRating($v, true);
            }

            foreach ($data['photos'] as $photo) {
                /** @var UserPhoto $p */
                $p = UserPhoto::find($photo);
                $r = new PhotoRepository($p);
                $r->setIncludedInRating($p, true);
            }
        } else {
            // Fill empty bang description
            $data['description'] = $data['description'] ?? '';
            $data['is_private'] = $data['is_private'] == 1 ? true : false;
        }

        $spamService = new SpamService;

        $data['description'] = $spamService->replaceRestrictedWords($data['description']);
        $data['title'] = $spamService->replaceRestrictedWords($data['title']);

        if (strlen($data['title']) > 50) {
            $data['title'] = substr($data['title'], 0, 50);
        }

        if (strlen($data['description']) > 3000) {
            $data['description'] = substr($data['description'], 0, 3000);
        }

        if ($data['type'] === Event::TYPE_BANG || $data['type'] === Event::TYPE_FUN) {
            $data['chemsfriendly'] = (int)$data['chemsfriendly'];
        }

        $data['is_sticky'] = $user->isStaff();

        if (isset($data['lat']) && isset($data['lng'])) {
            $data['location_geom'] = new Point($data['lat'], $data['lng'], 4326);	// (lat, lng)
        }

        // Photos to sync
        $syncPhotosPayload = [];

        // Update preview photo
        if (!empty($data['preview_photo'])) {
            $previewPhoto = $data['preview_photo'];
            if (!empty($previewPhoto['id'])) {
                /** @var UserPhoto $p */
                $p = UserPhoto::find($previewPhoto['id']);

                if (!empty($p)) {
                    $makeVisibleTo = 'public';
                    $r = new PhotoRepository($p);
                    $r->setIncludedInRating($p, $makeVisibleTo);
                }

                $syncPhotosPayload[$previewPhoto['id']] = ['is_default' => 'yes'];
                // Set event's main photo
                $data['photo'] = $previewPhoto['photo'];
            }
        }

        // Collect photos
        if (
            $data['type'] !== Event::TYPE_BANG
            &&
            !empty($data['photos'])
        ) {
            $setFirstPhotoAsPreview = empty($data['preview_photo']);
            foreach ($data['photos'] as $photoId) {
                $syncPhotosPayload[$photoId] = ['is_default' => $setFirstPhotoAsPreview ? 'yes' : 'no'];
                $setFirstPhotoAsPreview = false;
            }
        }

        // Remove non-db fields
        $nativeParams = collect($data)
            ->except([
                'photos',
                'videos',
                'tags',
                'preview_photo'
            ])
            ->toArray();

        // Create/update an event
        if (empty($event)) {
            $nativeParams['user_id'] = $user->id;

            $event = $this->createEvent($nativeParams);
            $membership = new EventMembership();
            $membership->user_id = $user->id;
            $membership->event_id = $event->id;
            $membership->status = EventMembership::STATUS_HOST;
            $membership->save();
        } else {
            $this->updateObject($event, $nativeParams);
        }

        // Sync photos
        if (!empty($syncPhotosPayload)) {
            $event->photos()->sync($syncPhotosPayload);

            // Get photos without rating
            $nullRatingPhotos = $userPhotoRepository
                ->whereIn('id', array_keys($syncPhotosPayload))
                ->whereNull('nudity_rating')
                ->get();

            // Set rating
            if (!empty($nullRatingPhotos)) {
                foreach ($nullRatingPhotos as $photo) {
                    /** @var UserPhoto $photo */
                    $photo->updateNudityRating(false);
                }
            }
        }

        // Handle videos
        if (
            $data['type'] !== Event::TYPE_BANG
            &&
            !empty($data['videos'])
        ) {
            $event->videos()->sync($data['videos']);
        }

        // Sync tags
        if (
            $type !== Event::TYPE_BANG
            &&
            !empty($data['tags'])
        ) {
            $tagsIds = [];
            foreach ($data['tags'] as $tagName) {
                $tagName['name'] = $spamService->replaceRestrictedWords($tagName['name']);

                if (empty($tagName)) {
                    continue;
                }
                $tag = $tagRepository->findOrCreateTag($tagName['name']);
                $tagsIds[$tag['id']] = $tag['id'];
            }
            $event->tags()->sync($tagsIds);
        } else if ($type !== Event::TYPE_BANG && empty($data['tags'])) {
            $event->tags()->detach();
        }

        /* Request data from DB until it's retrieved */
        $stop = false;
        while ($stop == false) {
            try {
                $event->refresh();
                $stop = true;
            } catch (Exception $e) {
                sleep(1);
            }
        }

        return $event;
    }
}
