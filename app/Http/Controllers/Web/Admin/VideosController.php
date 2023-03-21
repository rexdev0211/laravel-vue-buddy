<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\Controller;
use App\Repositories\VideoRepository;
use App\User;
use App\UserVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideosController extends Controller
{
    /**
     * @var VideoRepository
     */
    protected $videoRepository;

    /**
     * VideosController constructor.
     * @param VideoRepository $videoRepository
     */
    public function __construct(VideoRepository $videoRepository)
    {
        $this->videoRepository = $videoRepository;
    }

    /**
     * @return View
     */
    public function index(): view
    {
        $resetFilter = request()->exists('resetFilters');
        $filterName = request()->get('username');
        $filterId = request()->get('id');
        $filterEmail = request()->get('email');

        $startRating = config('const.START_NUDITY_RATING');

        $rated = UserVideo::with('user')
                            ->whereHas('user', function ($query) {
                                $query->whereNull('deleted_at')
                                      ->where('status', 'active');
                            })
                            ->whereNotNull('nudity_rating')
                            ->where(function($q) {
                                $q->where('visible_to', 'public')
                                    ->orWhere('is_included_in_rating', true);
                            });

        $unrated = UserVideo::with('user')
                            ->whereHas('user', function ($query) {
                                $query->whereNull('deleted_at')
                                      ->where('status', 'active');
                            })
                            ->whereNotNull('nudity_rating')
                            ->where(function($q) {
                                $q->where('visible_to', 'public')
                                    ->orWhere('is_included_in_rating', true);
                            });

        $only = request()->get('rating');

        $counters = [
            'rated'   => 0,
            'unrated' => 0
        ];

        if ($only === 'unrated' || is_null($only)) {
            $counters['rated'] = $rated->where('manual_rating', '!=', UserVideo::RATING_UNRATED)->count();
            $counters['unrated'] = $unrated->where('manual_rating', UserVideo::RATING_UNRATED)->count();
        } else if ($only === 'safe') {
            $counters['rated'] = $rated->where('manual_rating', UserVideo::RATING_CLEAR)->count();
        } else if ($only === 'not_safe') {
            $counters['rated'] = $rated->where('manual_rating', UserVideo::RATING_ADULT)->count();
        } else if ($only === 'prohibited') {
            $counters['rated'] = $rated->where('manual_rating', UserVideo::RATING_PROHIBITED)->count();
        } else if ($only === 'all') {
            $counters['rated'] = $rated->where('manual_rating', '!=', UserVideo::RATING_UNRATED)->count();
            $counters['unrated'] = $unrated->where('manual_rating', UserVideo::RATING_UNRATED)->count();
        }

        $videos = $this->videoRepository->getModerationVideosList($only, $filterId, $filterName, $filterEmail);

        $blockedRating = [UserVideo::RATING_ADULT, UserVideo::RATING_PROHIBITED];

        return view('admin.moderation.videos', [
            'startRating' => $startRating,
            'only'     => request()->get('rating'),
            'blockedRating' => $blockedRating,
            'counters' => $counters,
            'videos'   => $videos,
            'email' => $filterEmail,
            'userId' => $filterId,
            'username' => $filterName
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function rate(): JsonResponse
    {
        $allowedTypes = [
            UserVideo::RATING_CLEAR,
            UserVideo::RATING_ADULT,
            UserVideo::RATING_PROHIBITED
        ];

        $id = request()->get('id');
        $type = request()->get('type');

        if (!in_array($type, $allowedTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected rate type.',
            ]);
        }

        $video = UserVideo::find($id);

        if (!$video) {
            return response()->json([
                'success' => false,
                'message' => 'Video is not found.',
            ]);
        }

        $video->manual_rating = $type;
        $video->status = 'processed';
        $video->reviewed_by = auth()->user()->id;
        $video->reviewed_at = date('Y-m-d H:i:s', strtotime('now'));
        $video->save();

        if ($video->isProhibited() || ($video->slot == 'clear' && $video->isAdult())) {
            event(new \App\Events\ShowErrorNotification([
                'user_to' => $video->user->id,
                'message' => 'videoRejectedNotification',
            ]));
        }

        return response()->json([
            'success' => true,
            'message' => 'Video successfully rated.',
            'id'      => $id,
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function rateGroup(): JsonResponse
    {
        $allowedTypes = UserVideo::GENERAL_RATINGS;

        $ids  = request()->get('ids');
        $type = request()->get('type');

        if (!in_array($type, $allowedTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected rate type.',
            ]);
        }

        $videos = UserVideo::whereIn('id', $ids)->get();

        if (count($videos) != count($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more videos is not found. Please refresh page and try again',
            ]);
        }

        UserVideo::whereIn('id', $ids)
                ->update([
                    'manual_rating' => $type,
                    'status'        => 'processed',
                    'reviewed_by'   => auth()->user()->id,
                    'reviewed_at'   => date('Y-m-d H:i:s', strtotime('now')),
                ]);

        foreach ($ids as $id) {
            $video = UserVideo::find($id);

            if ($video->isProhibited() || ($video->slot == 'clear' && $video->isAdult())) {
                event(new \App\Events\ShowErrorNotification([
                    'user_to' => $video->user->id,
                    'message' => 'videoRejectedNotification',
                ]));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Videos successfully rated.',
        ]);
    }

    /**
     * @param $id
     * @param $rate
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function rateHard($id, $rate)
    {
        UserVideo::where('id', $id)
                ->update([
                    'manual_rating' => $rate,
                    'status'        => 'processed',
                    'reviewed_by'   => auth()->user()->id,
                    'reviewed_at'   => date('Y-m-d H:i:s', strtotime('now')),
                ]);

        $video = UserVideo::find($id);

        if ($video->isProhibited() || ($video->slot == 'clear' && $video->isAdult())) {
            event(new \App\Events\ShowErrorNotification([
                'user_to' => $video->user->id,
                'message' => 'videoRejectedNotification',
            ]));
        }

        return redirect(route('admin.videosModeration'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteVideo($id)
    {
        UserVideo::where('id', $id)->delete();

        return redirect(route('admin.moderation.videos'));
    }
}
