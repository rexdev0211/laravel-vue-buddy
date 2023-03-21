<?php

namespace App\Http\Controllers\Mobile\V1;

use Carbon\Carbon;
use App\UserPhoto;
use App\Models\Rush\Rush;
use App\Models\Rush\RushStrip;
use App\Models\Rush\RushMedia;
use App\Models\Rush\RushFavorite;

class RushController extends Controller
{
    /**
     * Get Rush data for authenticated user
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rushesData = Rush::getStripsAndFavoritesData();

        $myRushes = Rush::with('latest_strip')
                        ->where('user_id', auth()->user()->id)
                        ->whereIn('status', ['active'])
                        ->orderBy('id', 'DESC')
                        ->get()
                        ->map(function($item) {
                            return $item->formatForView();
                        });

        $userImages = UserPhoto::where('user_id', auth()->user()->id)
                               ->get()
                               ->map(function($item) {
                                   return $item->formatForRushView();
                               });

        $rushImages = RushMedia::where('user_id', auth()->user()->id)
                               ->orderBy('id', 'DESC')
                               ->get()
                               ->map(function($item) {
                                   return $item->formatForView();
                               });

        $updateUser = false;
        if (auth()->user()->latest_widget != 'rush') {
            auth()->user()->latest_widget = 'rush';
            $updateUser = true;
        }

        $widgetAnnounce = auth()->user()->widget_announce;
        if ($widgetAnnounce == 'announced') {
            auth()->user()->widget_announce = 'rush.welcome';
            $updateUser = true;
        }

        if ($updateUser) {
            auth()->user()->save();
        }

        return response()->json([
            'success'         => true,
            'rushes'          => $rushesData['rushes'],
            'myRushes'        => $myRushes,
            'userImages'      => $userImages,
            'rushImages'      => $rushImages,
            'me'              => auth()->user()->formatForRush(),
            'favorites'       => $rushesData['favorites'],
            'queue'           => $rushesData['queue'],
            'widgetAnnounced' => $widgetAnnounce,
        ]);
    }

    /**
     * Refresh rushes data
     *
     * @return \Illuminate\Http\Response
     */
    public function refresh()
    {
        $rushesData = Rush::getStripsAndFavoritesData();

        return response()->json($rushesData);
    }

    /**
     * Get Rush data by Id for View page
     *
     * @return \Illuminate\Http\Response
     */
    public function getRush($id)
    {
        $now  = Carbon::now();
        $rush = Rush::with('author')
                    ->with('strips')
                    ->with('favorites')
                    ->with('strips.applauses')
                    ->with('strips.rank')
                    ->with('latest_viewed_strip')
                    ->where('id', $id)
                    ->whereIn('status', ['active'])
                    ->first();

        if (!$rush) {
            return response()->json([
                'success' => false,
                'message' => 'Strip not found.',
            ]);
        }

        $latestViewedStripId = $rush->latest_viewed_strip ? $rush->latest_viewed_strip->strip_id : $rush->strips->first()->id;

        return response()->json([
            'success' => true,
            'rush'    => [
                'id'       => $rush->id,
                'title'    => $rush->title,
                'favorite' => $rush->favorite,
                'author'   => $rush->author->formatForRush(),
                'streak'   => $now->diffInDays($rush->created_at),
                'strips'   => $rush->strips->map(function($strip){
                    return $strip->formatForView(true);
                }),
                'latestViewedStripId' => $latestViewedStripId > $rush->strips->last()->id ? $rush->strips->last()->id : $latestViewedStripId,
            ],
        ]);
    }

    /**
     * Mark Strip as Viewed by Authenticated user
     *
     * @return \Illuminate\Http\Response
     */
    public function markStripViewed($rushId, $stripId)
    {
        $strip = RushStrip::where('id', $stripId)
                          ->where('rush_id', $rushId)
                          ->first();

        if (!$strip) {
            return response()->json([
                'success' => false,
                'message' => 'Slide not found.',
            ]);
        }

        $strip->markStripViewed(auth()->user()->id);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get Rush data by Id for Edit page
     *
     * @return \Illuminate\Http\Response
     */
    public function getEditRush($id)
    {
        $rush = Rush::with('strips')
                    ->where('id', $id)
                    ->whereIn('status', ['active'])
                    ->where('user_id', auth()->user()->id)
                    ->first();

        if (!$rush) {
            return response()->json([
                'success' => false,
                'message' => 'Strip not found.',
            ]);
        }

        return response()->json([
            'success' => true,
            'rush'    => [
                'id'     => $rush->id,
                'title'  => $rush->title,
                'streak' => Carbon::now()->diffInDays($rush->created_at),
                'strips' => $rush->strips->map(function($strip){
                    return $strip->formatForView();
                }),
            ],
        ]);
    }

    /**
     * Create Rush Strip
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->validate(request(), [
            'type'       => 'required|in:bubble,image,video',
            'title'      => 'required|string|max:29',
            'message'    => 'required_if:type,bubble|string|nullable',
            'image_path' => 'required_if:type,image|string|nullable',
        ]);

        $type = request()->get('type');

        $rush_id = request()->get('rush_id');

        if (!$rush_id) {
            if (!auth()->user()->isPro()) {
                $userStrips = Rush::where('user_id', auth()->user()->id)
                                  ->where('status', 'active')
                                  ->count();

                if ($userStrips >= config('const.RUSH_STRIPS_LIMIT')) {
                    return response('PRO required.', 422);
                }
            }

            $rush = new Rush;
            $rush->title   = request()->get('title');
            $rush->user_id = auth()->user()->id;
            $rush->save();
        } else {
            $rush = Rush::where('id', $rush_id)
                        ->where('user_id', auth()->user()->id)
                        ->first();

            if (!$rush) return response('No Rush with this id found.', 422);

            $rush->title = request()->get('title');
            $rush->save();
        }

        $strip = new RushStrip;
        $strip->rush_id          = $rush->id;
        $strip->type             = $type;
        $strip->video_id         = null;
        $strip->video_path       = null;
        $strip->profile_attached = request()->get('profile_attached') ? true : false;
        switch ($type) {
            case 'bubble':
                $strip->message    = request()->get('message');
                $strip->image_id   = null;
                $strip->image_path = null;
                break;
            case 'image':
                $strip->message    = '';
                $strip->image_id   = request()->get('image_id');
                $strip->image_path = request()->get('image_path');
        }
        $strip->save();

        return response()->json([
            'success' => true,
            'newRush' => $rush_id ? false : true,
            'rush'    => $rush->formatForView(),
        ]);
    }

    /**
     * Create Rush Strip
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadImage()
    {
        $this->validate(request(), [
            'image' => 'required|image'
        ]);

        $image = new RushMedia;
        $image->type    = 'image';
        $image->user_id = auth()->user()->id;
        $uploaded = $image->tryUpload(request()->file('image'));

        if ($uploaded) $image->save();
        else {
            response()->json([
                'success' => false,
                'message' => 'Image upload failure.',
            ]);
        }

        return response()->json([
            'success' => true,
            'image'   => $image->formatForView(),
        ]);
    }

    /**
     * Delete Rush Strip
     *
     * @return \Illuminate\Http\Response
     */
    public function delete()
    {
        $this->validate(request(), [
            'strip_id' => 'required|integer'
        ]);

        $strip = RushStrip::with('rush')
                          ->with('rush.strips')
                          ->where('id', request()->get('strip_id'))
                          ->whereHas('rush', function($query) {
                              $query->where('user_id', auth()->user()->id);
                          })
                          ->first();

        if (!$strip) {
            return response()->json([
                'success' => false,
                'message' => 'Slide not found.',
            ]);
        }

        $stripId     = $strip->id;
        $rushId      = $strip->rush->id;
        $rushDeleted = false;
        if ($strip->rush->strips->count() <= 1) {
            $rushDeleted = true;

            $strip->rush->clear();
            $strip->rush->delete();
        }

        $strip->clear(!$rushDeleted);
        $strip->delete();

        $rush = null;
        if (!$rushDeleted) {
            $rush = Rush::with('latest_strip')
                        ->where('id', $rushId)
                        ->where('user_id', auth()->user()->id)
                        ->first()
                        ->formatForView();
        }

        return response()->json([
            'id'          => $stripId,
            'success'     => true,
            'rush'        => $rush,
            'rushId'      => $rushId,
            'rushDeleted' => $rushDeleted,
        ]);
    }

    /**
     * Give applause to a strip
     *
     * @return \Illuminate\Http\Response
     */
    public function applause($rushId, $stripId)
    {
        $this->validate(request(), [
            'claps' => 'required|integer',
        ]);

        $strip = RushStrip::with('rush') // need for update rank in RushStrip::applause function
                          ->where('id', $stripId)
                          ->first();

        if (!$strip) {
            return response()->json([
                'success' => false,
                'message' => 'Strip not found.',
            ]);
        }

        $claps = request()->get('claps') > 10 ? 10 : request()->get('claps');
        $strip->applause($claps);

        $rank = $strip->rank;

        return response()->json([
            'success'     => true,
            'stripId'     => $strip->id,
            'claps'       => $claps,
            'total_claps' => $rank ? $rank->applauses_count : $claps,
        ]);
    }

    /**
     * Give applause to a strip
     *
     * @return \Illuminate\Http\Response
     */
    public function favorite($rushId)
    {
        $rush = Rush::where('id', $rushId)
                    ->whereIn('status', ['active'])
                    ->first();

        if (!$rush) {
            return response()->json([
                'success' => false,
                'message' => 'Rush not found.',
            ]);
        }

        if (!auth()->user()->isPro() && !$rush->isFavoriteForUser(auth()->user()->id)) {
            $userFavorites = RushFavorite::where('user_id', auth()->user()->id)->count();

            if ($userFavorites >= config('const.RUSH_FAVORITES_LIMIT')) {
                return response('PRO required.', 422);
            }
        }

        $favorite = $rush->favoriteToggle(auth()->user()->id);

        return response()->json([
            'success'   => true,
            'favorite'  => $favorite,
            'favorites' => Rush::getFavoritesData(),
        ]);
    }

    /**
     * Change user announce state
     *
     * @return \Illuminate\Http\Response
     */
    public function changeUserAnnounceState()
    {
        auth()->user()->widget_announce = request()->get('type');
        auth()->user()->save();

        return response()->json([
            'success' => true,
        ]);
    }
}
