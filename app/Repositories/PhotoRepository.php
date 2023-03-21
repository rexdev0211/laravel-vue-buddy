<?php namespace App\Repositories;

use App\UserPhoto;
use App\UserVideo;

/**
 * Class UserPhotoRepository
 * @package App\Repositories
 */
class PhotoRepository extends BaseRepository
{
    /**
     * UserPhotoRepository constructor.
     * @param UserPhoto $model
     */
    public function __construct(UserPhoto $model = null)
    {
        if (empty($model)){
            $model = new UserPhoto();
        }
        parent::__construct($model);
    }

    /**
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createUserPhoto($data)
    {
        return $this->create($data);
    }

    /**
     * @param $userId
     * @param bool $visibleTo
     * @param bool $isDefault
     * @return mixed
     */
    public function getGalleryPhotosByUserId($userId, $visibleTo = false, $isDefault = false)
    {
        return $this
            ->where('user_id', $userId)
            ->filterBy('visible_to', $visibleTo)
            ->filterBy('is_default', $isDefault)
            ->orderByRaw("is_default = 'yes' desc") //show default photo on top
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * @param $page
     * @param $perPage
     * @param $orderBy
     * @param $orderBySort
     * @return mixed
     */
    public function getPhotosList($page, $perPage, $orderBy, $orderBySort, $filterDefault, $filterVisible, $filterNude, $filterOwnerId = null, $filterOwnerName = null, $filterManualNude = nulll) {
        $photos = $this->with('user')
                       ->filterBy('is_default', $filterDefault)
                       ->filterBy('visible_to', $filterVisible);

        if ($filterOwnerId) $photos = $photos->filterBy('user_id', $filterOwnerId);
        if ($filterOwnerName) $photos = $photos->whereHas('user', function($query) use ($filterOwnerName) {
                                                   $query->where('name', 'LIKE', "%{$filterOwnerName}%");
                                               });
        if ($filterManualNude) $photos = $photos->where('manual_rating', $filterManualNude);

        return $photos->filterByNudity($filterNude)
                      ->orderBy($orderBy, $orderBySort)
                      ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @param $filterNude
     * @return $this
     */
    public function filterByNudity($filterNude) {
        if(!$filterNude) return $this;

        $startRating = config('const.START_NUDITY_RATING');

        if ($filterNude == 'safe') {
            $this->where('nudity_rating', '<', $startRating);
        }
        elseif ($filterNude == 'not_safe') {
            $this->where('nudity_rating', '>', $startRating);
        }
        elseif ($filterNude == 'not_rated') {
            $this->whereNull('nudity_rating');
        }

        return $this;
    }

    /**
     * @param $id
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updatePhoto($id, $data) {
        return $this->update($id, $data);
    }

    /**
     * @param $userId
     * @param $photoId
     * @return UserPhoto
     */
    public function findUserPhoto($userId, $photoId)
    {
        return $this->where('user_id', $userId)->where('id', $photoId)->first();
    }

    /**
     * @param UserPhoto $photo
     * @param $newVisible
     * @return UserPhoto
     */
    public function setVisibleTo(UserPhoto $photo, $newVisible)
    {
        $newVisible = ($newVisible == 'public' ? 'public' : 'private');
        $data = ['visible_to' => $newVisible];
        if ($newVisible == 'private') {
            $data['is_default'] = 'no';
            $data['slot']       = null;
        }

        return $this->update($photo->id, $data);
    }

    public function setIncludedInRating(UserPhoto $photo, $val)
    {
        $val = (boolean) $val;
        $data = ['is_included_in_rating' => $val];

        if ($val === true && $photo->nudity_rating === null) {
            $data['nudity_rating'] = 0.001;
        }

        if ($val === true && $photo->manual_rating === null) {
            $data['manual_rating'] = UserPhoto::RATING_UNRATED;
        }

        return $this->update($photo->id, $data);
    }
}
