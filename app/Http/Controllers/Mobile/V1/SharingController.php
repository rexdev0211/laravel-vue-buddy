<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Http\Requests\ShareVideoRequest;
use App\Services\SharingService;
use App\SharingUrl;
use App\User;
use App\UserSharingUrl;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SharingController extends Controller
{
    /**
     * @var SharingService
     */
    protected $sharingService;

    /**
     * SharingController constructor.
     * @param SharingService $sharingService
     */
    public function __construct(SharingService $sharingService)
    {
        $this->sharingService = $sharingService;
    }

    /**
     * @param ShareVideoRequest $request
     * @return JsonResponse
     */
    public function shareVideos(ShareVideoRequest $request): JsonResponse
    {
        $currentUser = auth()->user();
        $videosIds = is_array($request->videosIds) && count($request->videosIds) > 0
            ? array_unique($request->videosIds)
            : [];

        $shareUrl = $this->sharingService->shareVideos($currentUser, $videosIds);

        return response()->json([
            'sharingUrl' => $shareUrl
        ], 200);
    }

    /**
     * @param $link
     * @return JsonResponse
     */
    public function checkLink($link): JsonResponse
    {
        try {
            $sharingLink = SharingUrl::where('url', $link)->first();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode());
        }

        return response()->json($sharingLink, 200);
    }

    /**
     * @param $link
     * @return JsonResponse
     */
    public function getSharedVideos($link): JsonResponse
    {
        try {
            $videos = $this->sharingService->getVideos($link);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode());
        }

        return response()->json($videos, 200);
    }

    /**
     * @param Request $request
     */
    public function deleteAllSharingLinks(Request $request)
    {
        /** @var User $me */
        $me = \Auth::user();

        /** @var UserSharingUrl $userSharingUrls */
        $userSharingUrls = UserSharingUrl::where('user_id', $me->id)->get();

        /** @var UserSharingUrl $userSharingUrl */
        foreach ($userSharingUrls as $userSharingUrl) {
            $userSharingUrl->delete();

            /** @var SharingUrl $sharingUrl */
            $sharingUrl = $userSharingUrl->sharingUrl;

            if (null === $sharingUrl) {
                continue;
            }

            $sharingUrl->status = SharingUrl::SHARING_STATUS_DISABLED;
            $sharingUrl->save();
        }

        return response('ok');
    }

    public function changeStatusLink(Request $request) {
        $me = auth()->user();

        $sharingUrl = SharingUrl::findOrFail($request->id);

        $userSharingUrls = UserSharingUrl::whereHas('sharingUrl', function ($query) use($request) {
            $query->where('id', $request->id);
        })
            ->where('user_id', $me->id)
            ->firstOrFail();

        $sharingUrl->status = !$request->status ? 'active': 'disabled';

        $sharingUrl->save();

        return response('ok');
    }

    public function saveSettingSharingLink(Request $request) {
        /** @var User $me */
        $me = auth()->user();

        if (!$me->isPro()) {
            return response()->json([
                'error' => 'Upgrade to PRO',
            ], 422);
        }

        $sharingUrl = SharingUrl::findOrFail($request->link_id);

        $userSharingUrls = UserSharingUrl::whereHas('sharingUrl', function ($query) use($request) {
            $query->where('id', $request->link_id);
        })
            ->where('user_id', $me->id)
            ->firstOrFail();

        $sharingUrl->views_limit = $request->views_limit;
        if($request->expire_at) {
            $sharingUrl->expire_at = Carbon::parse($request->expire_at.' '. $request->time);
        }
        else {
            $sharingUrl->expire_at = null;
        }

        $sharingUrl->save();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllSharingLinks(Request $request)
    {
        /** @var User $me */
        $me = \Auth::user();

        /** @var UserSharingUrl $userSharingUrls */
        $userSharingUrls = UserSharingUrl::with([
            'sharingUrl.sharingVideos'
        ])
            ->where('user_id', $me->id)
            ->get();


        foreach ($userSharingUrls as $userSharingUrl) {
            $video = $userSharingUrl->sharingUrl->sharingVideos[0] ?? false;

            if (false === $video) {
                $userSharingUrl->delete();
                continue;
            }

            $thumbnail = $video->getThumbnailUrl();
            $userSharingUrl->thumbnail = $thumbnail;


            if($userSharingUrl->sharingUrl->expire_at) {
                $userSharingUrl->sharingUrl->hours = Carbon::parse($userSharingUrl->sharingUrl->expire_at)->format('H:i');
            }

            $userSharingUrl->sharingUrl->created_at_format = $userSharingUrl->sharingUrl->created_at->format('Y-m-d H:i');
        }

        $userSharingUrls = $userSharingUrls->toArray();

        return response()->json([
            'links' => $userSharingUrls,
        ]);
    }
}
