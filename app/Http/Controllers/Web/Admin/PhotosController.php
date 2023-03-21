<?php namespace App\Http\Controllers\Web\Admin;

/**
 * Admin Photos Controller
 */

use DB;
use App\UserPhoto;
use App\Http\Controllers\Web\Controller;

class PhotosController extends Controller
{
    /**
     * Get Photos for Moderation
     * @return \Illuminate\View\View
     */
    public function index() {
        $startRating = config('const.START_NUDITY_RATING');

        // Get counters
        $rated   = UserPhoto::with('user')
                            ->whereHas('user', function($query) {
                                $query->whereNull('deleted_at')
                                      ->where('status', 'active');
                            })
                            ->whereNotNull('nudity_rating')
                            ->where(function($q) {
                                $q->where('visible_to', 'public')
                                    ->orWhere('is_included_in_rating', true);
                            });
        $unrated = UserPhoto::with('user')
                            ->whereHas('user', function($query) {
                                $query->whereNull('deleted_at')
                                      ->where('status', 'active');
                            })
                            ->whereNotNull('nudity_rating')
                            ->where(function($q) {
                                $q->where('visible_to', 'public')
                                    ->orWhere('is_included_in_rating', true);
                            });

        if (request()->get('only') == 'safe') {
            $rated   = $rated->where('nudity_rating', '<=', $startRating);
            $unrated = $unrated->where('nudity_rating', '<=', $startRating);
        } elseif (request()->get('only') == 'not_safe') {
            $rated   = $rated->where('nudity_rating', '>', $startRating);
            $unrated = $unrated->where('nudity_rating', '>', $startRating);
        }

        $counters = [
            'rated'   => $rated->where('status', 'reviewed')->count(),
            'unrated' => $unrated->where('manual_rating', UserPhoto::RATING_UNRATED)->count(),
        ];

        // Get Photos
        $joinWhere = '';
        if (request()->get('only') == 'safe') {
            $joinWhere = "AND user_photos.nudity_rating <= '{$startRating}'";
        } elseif (request()->get('only') == 'not_safe') {
            $joinWhere = "AND user_photos.nudity_rating > '{$startRating}'";
        }

        $joinWhere .= " AND user_photos.manual_rating = '" . UserPhoto::RATING_UNRATED . "'";

        $photos = DB::select("SELECT users.id AS user_id, users.name, user_photos.photo, user_photos.id AS photo_id, user_photos.nudity_rating
            FROM users
            JOIN user_photos ON user_photos.user_id = users.id
                AND (user_photos.visible_to = 'public' || user_photos.is_included_in_rating=true)
                AND user_photos.nudity_rating IS NOT NULL
                $joinWhere
            WHERE users.status = 'active'
              AND users.deleted_at IS NULL
            ORDER BY users.last_login DESC, user_photos.id DESC
            LIMIT 16
        ");

        $photos = collect($photos)->map(function ($item) {
                                      return (object) [
                                          'id'            => $item->photo_id,
                                          'photo'         => $item->photo,
                                          'nudity_rating' => $item->nudity_rating,
                                          'url'           => UserPhoto::getUrlByRelativePath($item->photo),
                                          'url_orig'      => UserPhoto::getUrlByRelativePath($item->photo, 'orig'),
                                          'user'          => (object) [
                                              'id'         => $item->user_id,
                                              'name'       => $item->name,
                                          ],
                                      ];
                                  });

        return view('admin.moderation.photos', [
            'startRating' => $startRating,
            'only'     => request()->get('only'),
            'counters' => $counters,
            'photos'   => $photos,
        ]);
    }

    /**
     * Rate Photo
     * @return \Illuminate\Http\Response
     */
    public function rate() {
        $allowedTypes = [
            UserPhoto::RATING_CLEAR,
            UserPhoto::RATING_SOFT,
            UserPhoto::RATING_ADULT,
            UserPhoto::RATING_PROHIBITED
        ];

        $id   = request()->get('id');
        $type = request()->get('type');

        if (!in_array($type, $allowedTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected rate type.',
            ]);
        }

        $photo = UserPhoto::find($id);

        if (!$photo) {
            return response()->json([
                'success' => false,
                'message' => 'Photo is not found.',
            ]);
        }

        $photo->manual_rating = $type;
        $photo->status        = 'reviewed';
        $photo->reviewed_by   = auth()->user()->id;
        $photo->reviewed_at   = date('Y-m-d H:i:s', strtotime('now'));
        $photo->save();

        if ($photo->isProhibited() || ($photo->slot == 'clear' && $photo->isAdult())) {
            event(new \App\Events\ShowErrorNotification([
                'user_to' => $photo->user->id,
                'message' => 'photoRejectedNotification',
            ]));
        }

        return response()->json([
            'success' => true,
            'message' => 'Photo successfully rated.',
            'id'      => $id,
        ]);
    }

    /**
     * Rate Group of Photos
     * @return \Illuminate\Http\Response
     */
    public function rateGroup() {
        $allowedTypes = UserPhoto::GENERAL_RATINGS;

        $ids  = request()->get('ids');
        $type = request()->get('type');

        if (!in_array($type, $allowedTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected rate type.',
            ]);
        }

        $photos = UserPhoto::whereIn('id', $ids)
                           ->get();

        if (count($photos) != count($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more photos is not found. Plese refresh page and try again',
            ]);
        }

        UserPhoto::whereIn('id', $ids)
                 ->update([
                     'manual_rating' => $type,
                     'status'        => 'reviewed',
                     'reviewed_by'   => auth()->user()->id,
                     'reviewed_at'   => date('Y-m-d H:i:s', strtotime('now')),
                 ]);

        foreach ($ids as $id) {
            $photo = UserPhoto::find($id);

            if ($photo->isProhibited() || ($photo->slot == 'clear' && $photo->isAdult())) {
                event(new \App\Events\ShowErrorNotification([
                    'user_to' => $photo->user->id,
                    'message' => 'photoRejectedNotification',
                ]));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Photos successfully rated.',
        ]);
    }

    /**
     * Rate Photo by GET request with id and rate
     * @return \Illuminate\Http\Response
     */
    public function rateHard($id, $rate) {
        UserPhoto::where('id', $id)
                 ->update([
                     'manual_rating' => $rate,
                     'status'        => 'reviewed',
                     'reviewed_by'   => auth()->user()->id,
                     'reviewed_at'   => date('Y-m-d H:i:s', strtotime('now')),
                 ]);

        $photo = UserPhoto::find($id);

        if ($photo->isProhibited() || ($photo->slot == 'clear' && $photo->isAdult())) {
            event(new \App\Events\ShowErrorNotification([
                'user_to' => $photo->user->id,
                'message' => 'photoRejectedNotification',
            ]));
        }

        return redirect(route('admin.photosModeration'));
    }

    /**
     * Delete image by GET request with id
     * @return \Illuminate\Http\Response
     */
    public function deleteImage($id) {
        UserPhoto::where('id', $id)
                 ->delete();

        return redirect(route('admin.photosModeration'));
    }
}
