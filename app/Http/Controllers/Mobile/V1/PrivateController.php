<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Repositories\MessageRepository;
use App\Repositories\TagRepository;
use App\Repositories\PhotoRepository;
use App\Repositories\VideoRepository;
use App\Services\MediaService;
use App\Services\SpamService;
use App\UserPhoto;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PrivateController extends Controller
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    /**
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function addGalleryPhoto(): JsonResponse
    {
        $user = request()->user();
        $this->validate(request(), [
            'photo' => 'required|imageable'
        ]);

        try {
            $photo = (new MediaService())->uploadGalleryPhoto($user, request()->file('photo'));
            $photo->setUrls(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }

        return response()->json($photo);
    }

    /**
     * @param int $photoId
     *
     * @return JsonResponse
     */
    public function updateGalleryPhoto(int $photoId): JsonResponse
    {
        $actions = json_decode(request()->get('actions'), true);
        try {
            $photo = (new MediaService())->changeGalleryPhoto(
                request()->user(),
                $photoId,
                request()->file('photo_orig'),
                $actions['rotation'] ?? 0,
                $actions['crop'] ?? []
            );
            $photo->setUrls(true);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage()
            ], 422);
        }

        return response()->json($photo);
    }

    /**
     * @param int $photoId
     * @param string $slot
     *
     * @return JsonResponse
     */
    public function setPhotoAsDefault(int $photoId, string $slot): JsonResponse
    {
        $validator = Validator::make(['slot' => $slot], [
            'slot' => 'required|in:clear,adult'
        ]);
        if ($validator->fails()){
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        $user = request()->user();
        $photo = (new PhotoRepository)->findUserPhoto($user->id, $photoId);
        if (empty($photo)) {
            return response()->json([
                'error' => 'Photo not found'
            ], 422);
        }

        $photo->setAsDefault($slot);
        $photo->setUrls(true);
        $photo->fresh();

        if ($photo->slot == 'clear') {
            $pending = $photo->isAdult() && $photo->status === 'queued';
            $reject = ($photo->isAdult() || $photo->isProhibited()) && $photo->status === 'reviewed';

            $photo->pending = $pending;
            $photo->rejected = $reject;
        }

        return response()->json($photo);
    }

    /**
     * @param int $photoId
     * @param string $newVisible
     *
     * @return JsonResponse
     */
    public function changePhotoVisibleTo($photoId, $newVisible=null): JsonResponse
    {
        if (!empty($photoId) && empty($newVisible)) {
            $newVisible = $photoId; // merging web and api controller, I do not like it, but, it required.
            $photoId = null;
        }

        $user = request()->user();

        //check if user has free public pictures available
        if (
            $newVisible == 'public'
            &&
            !$user->isPro()
            &&
            $user->publicPhotos->count() >= config('const.MAX_PUBLIC_PICTURES_AMOUNT')
        ) {
            return response()->json([
                'error' => 'Maximum amount of public pictures has been reached'
            ], 422);
        }

        if (request()->has('photoIds')) {
            $photoRepository = new PhotoRepository();
            $photosIds = request()->get('photoIds');

            $updatedPhotos = [];

            foreach ($photosIds as $photoId) {
                /** @var UserPhoto $photo */
                $originalPhoto = $photoRepository->findUserPhoto($user->id, $photoId);

                if (!is_null($originalPhoto)) {
                    $updatedPhoto = $photoRepository->setVisibleTo($originalPhoto, $newVisible);
                    if ($newVisible == 'public') {
                        $updatedPhoto->updateNudityRating(false);
                    }
                    $updatedPhoto->setUrls(true);
                    $updatedPhotos[] = $updatedPhoto;
                }
            }

            return response()->json($updatedPhotos);
        } else {
            /** @var UserPhoto $photo */
            $originalPhoto = (new PhotoRepository())->findUserPhoto($user->id, $photoId);
            if (is_null($originalPhoto)) {
                return response()->json([
                    'error' => 'Photo doesn`t exist'
                ], 422);
            }

            $updatedPhoto = (new PhotoRepository())->setVisibleTo($originalPhoto, $newVisible);
            if ($newVisible == 'public') {
                $updatedPhoto->updateNudityRating(false);
            }
            $updatedPhoto->setUrls(true);

            return response()->json($updatedPhoto);
        }
    }

    /**
     * @param int $videoId
     * @param string $newVisible
     *
     * @return JsonResponse
     */
    public function changeVideoVisibleTo(int $videoId, string $newVisible): JsonResponse
    {
        $user = request()->user();

        //check if user has free public pictures available
        if ($newVisible == 'public' && !$user->isPro() && $user->publicVideos->count() >= config('const.MAX_PUBLIC_VIDEOS_AMOUNT')) {
            return response()->json([
                'error' => 'Maximum amount of public videos has been reached'
            ], 422);
        }

        $originalVideo = (new VideoRepository())->findUserVideo($user->id, $videoId);
        if (is_null($originalVideo)) {
            return response()->json([
                'error' => 'Video doesn`t exist'
            ], 422);
        }

        $updatedVideo = (new VideoRepository())->setVisibleTo($originalVideo, $newVisible);
        $updatedVideo->setUrls(true);

        return response()->json($updatedVideo);
    }

    /**
     * @param int $photoId
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function deletePhoto(int $photoId): JsonResponse
    {
        $user = request()->user();
        $photo = (new PhotoRepository)->findUserPhoto($user->id, $photoId);

        if (is_null($photo)) {
            return response()->json([
                'error' => "Photo doesn't exist"
            ], 422);
        }

        //delete photo from hdd
        $mediaService = new MediaService();
        $mediaService->deleteUserPhoto($photo->photo);

        // delete photo from messages
        (new MessageRepository())->detachImage($photo->id);
        $photo->delete();

        return response()->json('ok');
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteTag(int $id=null): JsonResponse
    {
        $me = request()->user();
        $tagId = request()->get('id');

        if (!empty($tagId)) {
            $me->tags()->detach($tagId);
        } else {
            $me->tags()->detach($id);
        }

        return response()->json('ok');
    }

    /**
     * @return JsonResponse
     */
    public function addTag(): JsonResponse
    {
        $me = request()->user();
        $tagName = request()->get('name');

        $spamService = new SpamService();
        $tagName = $spamService->replaceRestrictedWords($tagName);

        $tag = (new TagRepository())->findOrCreateTag($tagName);
        if ($me->tags->contains('id', $tag->id)) {
            return response()->json([], 204);
        }
        $me->tags()->syncWithoutDetaching([$tag->id]);

        return response()->json($tag);
    }
}
