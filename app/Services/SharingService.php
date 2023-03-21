<?php

namespace App\Services;

use App\Repositories\SharingRepository;
use App\Repositories\UserRepository;
use App\SharingUrl;
use App\User;

class SharingService
{
    /**
     * @param User $user
     * @param $videosIds
     * @return string
     */
    public function shareVideos(User $user, $videosIds): string
    {
        $userId = $user->id;

        $sharingRepository = new SharingRepository();
        $userRepository = new UserRepository();

        $sharingUrl = $this->createSharingUrl();

        $sharingData = $sharingRepository->saveSharing($userId, $sharingUrl, $videosIds);
        $userRepository->createUserSharingUrl($userId, $sharingData['id']);
        return config('const.SHARING_DOMAIN') . '/' . $sharingUrl;
    }

    /**
     * @param $link
     * @return array
     */
    public function getVideos($link): array
    {
        $videos = SharingUrl::with('sharingVideos')
                            ->where('url', $link)
                            ->where('status', SharingUrl::SHARING_STATUS_ACTIVE)
                            ->get()
                            ->pluck('sharingVideos')[0];

        return $videos->map(function ($video) {
            return [
                'video_url' => $video->getVideoUrl(),
                'thumb_orig' => $video->getThumbnailUrl()
            ];
        })->toArray();
    }

    /**
     * @return string
     */
    public function createSharingUrl(): string
    {
        while(true){
            $randomString = str_random(8);

            /** @var SharingUrl $checkExists */
            $checkExists  = SharingUrl::where('url', $randomString)->count();

            if ($checkExists > 0) {
                continue;
            }

            return $randomString;
        }
    }
}