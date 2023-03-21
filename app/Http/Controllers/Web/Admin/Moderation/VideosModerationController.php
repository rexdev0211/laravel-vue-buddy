<?php

namespace App\Http\Controllers\Web\Admin\Moderation;

use \App\Http\Controllers\Web\Controller;
use App\Repositories\UserRepository;
use App\Repositories\VideoRepository;
use App\UserVideo;
use Illuminate\Http\Request;
use Helper;
use Illuminate\View\View;

class VideosModerationController extends Controller
{
    /**
     * @param Request $request
     * @param VideoRepository $videoRepository
     * @return view
     */
    public function index(Request $request, VideoRepository $videoRepository): view
    {
        $sessionKey = 'admin.videos-moderation';

        $page        = (int)Helper::getUserPreference($sessionKey, 'page', 1);
        $perPage     = (int)Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());
        $orderBy     = Helper::getUserPreference($sessionKey, 'orderBy', 'id');
        $orderBySort = Helper::getUserPreference($sessionKey, 'orderBySort', 'desc');

        $resetForm        = $request->exists('resetFilters');
        $filterDefault    = Helper::getUserPreference($sessionKey, 'filterDefault', '', $resetForm);
        $filterVisible    = Helper::getUserPreference($sessionKey, 'filterVisible', '', $resetForm);
        $filterManualNude = Helper::getUserPreference($sessionKey, 'filterManualNude', '', $resetForm);
        $filterNude       = Helper::getUserPreference($sessionKey, 'filterNude', '', $resetForm);
        $filterOwnerName  = Helper::getUserPreference($sessionKey, 'filterOwnerName', '', $resetForm);
        $filterOwnerId    = Helper::getUserPreference($sessionKey, 'filterOwnerId', '', $resetForm);

        $videos = $videoRepository->getVideosList(
            $page, $perPage, $orderBy, $orderBySort,
            $filterDefault, $filterVisible, $filterNude, $filterOwnerId, $filterOwnerName, $filterManualNude
        );
//        dd($videos);
        $startRating = config('const.START_NUDITY_RATING');

        $defaultOptions = [
            '' => '--- Video type ---',
            'yes' => 'Profile videos',
            'no' => 'Other videos'
        ];

        $visibleOptions = [
            '' => '--- Visibility ---',
            'public' => 'Public',
            'private' => 'Private'
        ];

        $nudeOptions = [
            '' => '--- Nude options ---',
            'safe' => 'Safe',
            'not_safe' => 'Not safe',
            'not_rated' => 'Not rated',
        ];

        $manualNudeOptions = [
            '' => '--- Manual Nude options ---',
            UserVideo::RATING_UNRATED    => 'Unrated',
            UserVideo::RATING_CLEAR      => 'Clear',
            UserVideo::RATING_SOFT       => 'Soft',
            UserVideo::RATING_ADULT      => 'Hard',
            UserVideo::RATING_PROHIBITED => 'Prohibited',
        ];

        return view('admin.videosModeration.index', compact('videos', 'startRating', 'defaultOptions', 'visibleOptions',
            'nudeOptions', 'manualNudeOptions', 'sessionKey'));
    }

    public function rateHard()
    {

    }
}
