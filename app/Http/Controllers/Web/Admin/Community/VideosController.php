<?php

namespace App\Http\Controllers\Web\Admin\Community;

use Helper;

use App\UserVideo;

class VideosController extends \App\Http\Controllers\Web\Controller
{
    /**
     * Get Videos Management page
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sessionKey = 'admin.community.videos';

        $page        = (int)Helper::getUserPreference($sessionKey, 'page', 1);
        $perPage     = (int)Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());
        $orderBy     = Helper::getUserPreference($sessionKey, 'orderBy', 'id');
        $orderBySort = Helper::getUserPreference($sessionKey, 'orderBySort', 'desc');

        $resetForm        = request()->exists('resetFilters');
        $filterVisible    = Helper::getUserPreference($sessionKey, 'filterVisible', '', $resetForm);
        $filterManualNude = Helper::getUserPreference($sessionKey, 'filterManualNude', '', $resetForm);
        $filterOwnerName  = Helper::getUserPreference($sessionKey, 'filterOwnerName', '', $resetForm);
        $filterOwnerId    = Helper::getUserPreference($sessionKey, 'filterOwnerId', '', $resetForm);

        $videos = UserVideo::with('user')
                           ->whereHas('user', function($query) use ($filterOwnerName, $filterOwnerId) {
                               if ($filterOwnerName) {
                                   $query->whereNull('deleted_at')
                                         ->where('status', 'active')
                                         ->where('name', 'LIKE', '%'.$filterOwnerName.'%');
                               } elseif ($filterOwnerId) {
                                   $query->whereNull('deleted_at')
                                         ->where('status', 'active')
                                         ->where('id', $filterOwnerId);
                               } else {
                                   $query->whereNull('deleted_at')
                                         ->where('status', 'active');
                               }
                           })
                           ->whereIn('status', ['processed', 'accessible']);

        if ($filterVisible) {
            $videos = $videos->where('visible_to', $filterVisible);
        }

        if ($filterManualNude) {
            $videos = $videos->where('manual_rating', $filterManualNude);
        }

        $videos = $videos->orderBy('id', 'DESC')
                         ->paginate($perPage, ['*'], 'page', $page);

        $visibleOptions = [
            '' => '--- Visibility ---',
            'public' => 'Public',
            'private' => 'Private'
        ];

        $manualNudeOptions = [
            '' => '--- Manual Nude options ---',
            UserVideo::RATING_UNRATED    => 'Unrated',
            UserVideo::RATING_CLEAR      => 'Clear',
            UserVideo::RATING_SOFT       => 'Soft',
            UserVideo::RATING_ADULT      => 'Hard',
            UserVideo::RATING_PROHIBITED => 'Prohibited',
        ];

        return view('admin.community.videos', [
            'videos'            => $videos,
            'sessionKey'        => $sessionKey,
            'visibleOptions'    => $visibleOptions,
            'manualNudeOptions' => $manualNudeOptions,
        ]);
    }

    /**
     * Rate video
     * @param  integer $videoId
     * @param  string  $type
     * @return \Illuminate\Routing\Redirector
     */
    public function rate($videoId, $type)
    {
        $video = UserVideo::where('id', $videoId)->first();

        if (!$video || !in_array($type, [UserVideo::RATING_CLEAR, UserVideo::RATING_SOFT, UserVideo::RATING_ADULT, UserVideo::RATING_PROHIBITED])) {
            return abort(404);
        }

        $video->manual_rating = $type;
        $video->save();

        return redirect()->back();
    }

    /**
     * Delete video
     * @param  integer $videoId
     * @return \Illuminate\Routing\Redirector
     */
    public function delete($videoId)
    {
        $video = UserVideo::where('id', $videoId)->first();

        if (!$video) {
            return abort(404);
        }

        $video->deleteAllAssets();
        $video->delete();

        return redirect()->back();
    }
}
