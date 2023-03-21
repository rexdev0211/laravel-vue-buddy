<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Services\SpamService;
use App\Tag;
use App\User;
use Illuminate\Http\JsonResponse;
use App\Services\DiscoverService;
use App\Event;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return JsonResponse
     */
    public function getUsersAround(): JsonResponse
    {
        $request = request();

        $page = $request->get('page', 0);

        $currentUser = \Auth::user();
        \Log::info('currentUser' . json_encode($currentUser));
        $discoverService = new DiscoverService();

        try {
            $searchInput = $request->get('filterName', '');

            if (preg_match('/\#/', $searchInput)) {
                $discoverService->setFilterTags($searchInput);
            } else {
                $discoverService->setFilterName($searchInput);
            }

            $discoverService->setCurrentUser($currentUser);

            $filterType = $request->input('filterType', 'nearby') ?: 'nearby';
            $discoverService->setFilterType($filterType);

            if ($filterType != 'favorites') {
                $discoverService->setOnlyOnline($request->get('filterOnline'));
                $discoverService->setFilterPics($request->get('filterPics', false));
                $discoverService->setFilterVideos($request->get('filterVideos', false));
                $discoverService->setFilterAge($request->get('filterAge', false));
                $discoverService->setFilterHeight($request->get('filterHeight', false));
                $discoverService->setFilterWeight($request->get('filterWeight', false));
                $discoverService->setFilterPosition($request->get('filterPosition', false));
                $discoverService->setFilterBody($request->get('filterBody', false));
                $discoverService->setFilterPenis($request->get('filterPenis', false));
                $discoverService->setFilterDrugs($request->get('filterDrugs', false));
                $discoverService->setFilterHiv($request->get('filterHiv', false));
            } else {
                $discoverService->setOnlyOnline(false);
                $discoverService->setFilterPics(false);
                $discoverService->setFilterVideos(false);
                $discoverService->setFilterAge(false);
                $discoverService->setFilterHeight(false);
                $discoverService->setFilterWeight(false);
                $discoverService->setFilterPosition(false);
                $discoverService->setFilterBody(false);
                $discoverService->setFilterPenis(false);
                $discoverService->setFilterDrugs(false);
                $discoverService->setFilterHiv(false);
            }

            //$discoverService->setExceptIds($request->get('except', []));
            $discoverService->setPage($page);
            $discoverService->setPerPage(config('const.LOAD_USERS_AROUND_LIMIT'));
            $discoverService->setDistance(
                $request->get('distance', config('const.PRE_SEARCH_USERS_AROUND_KM'))
            );

            $rangedUsers = $discoverService->getRangedUsersAround();
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'usersAround' => [],
                'distance' => 0,
            ]);
        }

        return response()->json($rangedUsers);
    }

    /**
     * @return JsonResponse
     */
    public function eventsAround()
    {
        $currentUser = request()->user();
        $request = request();

        $categories = $request->get('categories', ['fun', 'sex']);
        $exceptIds = $request->get('except', []);
        $events = Event::getClosest($currentUser, $categories, $exceptIds);

        return response()->json($events);
    }

    public function getSearchTags(Request $request)
    {
        /*
         * CHECK
         */

        if (!$request->has('searchRequest') || empty($request->searchRequest)) {
            return response()->json([
                'success' => false,
            ]);
        }

        /*
         * GET TAGS
         */

        $searchRequest = trim($request->searchRequest);
        preg_match_all('/\#([^\s]+)/', $searchRequest, $tagsArray);

        if (empty($tagsArray[1])) {
            return response()->json([
                'success' => false,
            ]);
        }

        try {
            $tagsArray = $tagsArray[1];
            $tagsList = [];

            /*
             * 1. GET USER IDS
             * 2. CLEAR FROM RESTRICTED WORDS
             */

            $spamService = new SpamService;

            foreach ($tagsArray as $tag) {
                $tag = $spamService->replaceRestrictedWords($tag);

                if (empty($tag)) {
                    continue;
                }

                $checkTagsExists = Tag::where('name', 'like', $tag . '%')
                    ->whereHas('users', function($q) {
                        // ..
                    })
                    ->get();

                /** @var Tag $checkTagExist */
                foreach ($checkTagsExists as $checkTagExist) {
                    if (count($tagsList) > 100) {
                        break;
                    }

                    $tagsList[] = $checkTagExist;
                }
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            $tagsList = [];
        }

        /*
         * RETURN DATA
         */

        return response()->json([
            'tags' => $tagsList ?? [],
        ]);
    }
}
