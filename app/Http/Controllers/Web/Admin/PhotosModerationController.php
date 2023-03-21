<?php namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Controller;
use App\Repositories\PhotoRepository;
use App\Repositories\UserRepository;
use App\UserPhoto;
use Helper;
use Illuminate\Http\Request;

class PhotosModerationController extends Controller
{
    /**
     * @param PhotoRepository $userPhotoRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, PhotoRepository $userPhotoRepository, UserRepository $userRepository) {
        $sessionKey = 'admin.photos-moderation';

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

        $photos = $userPhotoRepository->getPhotosList($page, $perPage, $orderBy, $orderBySort,
            $filterDefault, $filterVisible, $filterNude, $filterOwnerId, $filterOwnerName, $filterManualNude);

        $startRating = config('const.START_NUDITY_RATING');

        $defaultOptions = [
            '' => '--- Photo type ---',
            'yes' => 'Profile photos',
            'no' => 'Other photos'
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
            UserPhoto::RATING_UNRATED    => 'Unrated',
            UserPhoto::RATING_CLEAR      => 'Clear',
            UserPhoto::RATING_SOFT       => 'Soft',
            UserPhoto::RATING_ADULT      => 'Hard',
            UserPhoto::RATING_PROHIBITED => 'Prohibited',
        ];

        return view('admin.photosModeration.index', compact('photos', 'startRating', 'defaultOptions', 'visibleOptions',
            'nudeOptions', 'manualNudeOptions', 'sessionKey'));
    }

    /**
     * @param $photoId
     * @param $type
     * @param PhotoRepository $userPhotoRepository
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeRating($photoId, $type, PhotoRepository $userPhotoRepository) {
        $rating = 1;

        if ($type == 'safe') {
            $rating = 0.001;
        }

        $userPhotoRepository->updatePhoto($photoId, ['nudity_rating' => $rating]);

        $photo = UserPhoto::find($photoId);

        if ($photo->isProhibited() || ($photo->slot == 'clear' && $photo->isAdult())) {
            event(new \App\Events\ShowErrorNotification([
                'user_to' => $photo->user->id,
                'message' => 'photoRejectedNotification',
            ]));
        }

        return redirect()->back();
    }
}
