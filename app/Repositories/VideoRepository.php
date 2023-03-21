<?php namespace App\Repositories;

use App\UserPhoto;
use App\UserVideo;

/**
 * Class UserVideoRepository
 * @package App\Repositories
 */
class VideoRepository extends BaseRepository
{
    /**
     * UserVideoRepository constructor.
     *
     * @param UserVideo $model
     */
    public function __construct(UserVideo $model = null)
    {
        if (empty($model)){
            $model = new UserVideo();
        }
        parent::__construct($model);
    }

    /**
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createUserVideo($data)
    {
        return $this->create($data);
    }

    /**
     * @param $userId
     * @param bool $visibleTo
     * @return UserVideo[]
     */
    public function getGalleryVideosByUserId($userId, $visibleTo = false)
    {
        return $this
            ->where('user_id', $userId)
            ->filterBy('visible_to', $visibleTo)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * @param $id
     * @param $data
     * @return UserVideo
     */
    public function updateVideo($id, $data) {
        return $this->update($id, $data);
    }

    /**
     * @param $userId
     * @param $videoId
     * @return UserVideo
     */
    public function findUserVideo($userId, $videoId)
    {
        return $this
            ->where('user_id', $userId)
            ->where('id', $videoId)
            ->first();
    }

    /**
     * @param UserVideo $video
     * @param $newVisible
     * @return UserVideo
     */
    public function setVisibleTo(UserVideo $video, $newVisible)
    {
        $newVisible = ($newVisible == 'public' ? 'public' : 'private');
        $data = ['visible_to' => $newVisible];

        return $this->update($video->id, $data);
    }

    /**
     * @param $page
     * @param $perPage
     * @param $orderBy
     * @param $orderBySort
     * @param $filterDefault
     * @param $filterVisible
     * @param $filterNude
     * @param null $filterOwnerId
     * @param null $filterOwnerName
     * @param null $filterManualNude
     * @return mixed
     */
    public function getVideosList($page, $perPage, $orderBy, $orderBySort, $filterDefault, $filterVisible, $filterNude, $filterOwnerId = null, $filterOwnerName = null, $filterManualNude = null)
    {
        $videos = $this->with('user')
                        ->filterBy('is_default', $filterDefault)
                        ->filterBy('visible_to', $filterVisible);

        if ($filterOwnerId) {
            $videos = $videos->filterBy('user_id', $filterOwnerId);
        }

        if ($filterOwnerName) {
            $videos = $videos->whereHas('user', function($query) use ($filterOwnerName) {
                $query->where('name', 'LIKE', "%{$filterOwnerName}%");
            });
        }

        if ($filterManualNude) {
            $videos = $videos->where('manual_rating', $filterManualNude);
        }

        $startRating = config('const.START_NUDITY_RATING');

        if ($filterNude == 'safe') {
            $videos = $videos->where('nudity_rating', '<', $startRating);
        } elseif ($filterNude == 'not_safe') {
            $videos = $videos->where('nudity_rating', '>', $startRating);
        } elseif ($filterNude == 'not_rated') {
            $videos = $videos->whereNull('nudity_rating');
        }

        return $videos->orderBy($orderBy, $orderBySort)
                      ->paginate($perPage, ['*'], 'page', $page);

    }

    /**
     * @param $filterNude
     * @param null $filterOwnerId
     * @param null $filterOwnerName
     * @param null $filterOwnerEmail
     * @return mixed
     */
    public function getModerationVideosList($filterNude, $filterOwnerId = null, $filterOwnerName = null, $filterOwnerEmail = null)
    {
        $videos = $this->with('user')
                           ->whereHas('user', function ($query) {
                               $query->where('status', 'active')
                                   ->whereNull('deleted_at')
                                   ->orderByDesc('last_login');
                           })
                           ->where('visible_to', 'public')
                           ->whereNotNull('nudity_rating');

        if ($filterOwnerId) {
            $videos = $videos->filterBy('user_id', $filterOwnerId);
        }

        if ($filterOwnerName) {
            $videos = $videos->whereHas('user', function($query) use ($filterOwnerName) {
                $query->where('name', 'LIKE', "%{$filterOwnerName}%");
            });
        }

        if ($filterOwnerEmail) {
            $videos = $videos->whereHas('user', function ($query) use ($filterOwnerEmail) {
                $query->where('email', 'like', '%' . $filterOwnerEmail . '%')
                      ->orWhere('email_orig', 'like', '%' . $filterOwnerEmail . '%');
            });
        }

        if ($filterNude === 'safe') {
            $videos = $videos->where('manual_rating', UserVideo::RATING_CLEAR);
        } else if ($filterNude === 'not_safe') {
            $videos = $videos->where('manual_rating', UserVideo::RATING_ADULT);
        } else if ($filterNude === 'prohibited') {
            $videos = $videos->where('manual_rating', UserVideo::RATING_PROHIBITED);
        } else if (is_null($filterNude) || $filterNude === 'unrated') {
            $videos = $videos->where('manual_rating', UserVideo::RATING_UNRATED);
        }

        $videos = $videos->limit(16)->get();

        return $videos->map(function ($video) {
            $video->setUrls(true);

            return (object) [
                'id'            => $video->id,
                'photo'         => $video->photo,
                'nudity_rating' => $video->nudity_rating,
                'manual_rating' => $video->manual_rating,
                'url'           => $video->getVideoUrl(),
                'url_orig'      => $video->getVideoUrl(),
                'user'          => (object) [
                    'id'         => $video->user_id,
                    'name'       => $video->getRelation('user')->name,
                ],
                'thumb_orig' => $video->thumb_orig,
                'thumb_small' => $video->thumb_small,
            ];
        });
    }

    public function setIncludedInRating(UserVideo $video, $val)
    {
        $val = (boolean) $val;
        $data = ['is_included_in_rating' => $val];

        if ($val === true && $video->nudity_rating === null) {
            $data['nudity_rating'] = 0.001;
        }

        if ($val === true && $video->manual_rating === null) {
            $data['manual_rating'] = UserPhoto::RATING_UNRATED;
        }

        return $this->update($video->id, $data);
    }
}
