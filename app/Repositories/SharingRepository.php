<?php

namespace App\Repositories;

use App\SharingUrl;
use App\UserSharingUrl;

class SharingRepository extends BaseRepository
{
    public function __construct(?SharingUrl $model = null)
    {
        if (empty($model)) {
            $model = new SharingUrl();
        }
        parent::__construct($model);
    }

    /**
     * @param $userId
     * @param $sharingUrl
     * @param $videosIds
     * @return mixed
     */
    public function saveSharing($userId, $sharingUrl, $videosIds)
    {
        return $this->persist($userId, $sharingUrl, $videosIds);
    }

    /**
     * @param $userId
     * @param $sharingUrl
     * @param $videosIds
     * @return mixed
     */
    public function persist($userId, $sharingUrl, $videosIds)
    {
        $prepareData = [
            'url' => $sharingUrl,
            'status' => SharingUrl::SHARING_STATUS_ACTIVE
        ];
        $sharing = $this->model->create($prepareData);
        $sharing->sharingVideos()->attach($videosIds);

        return $sharing;
    }
}