<?php

namespace App\Http\Controllers\Mobile\V1;

use Validator;
use getID3;

use App\Jobs\ConvertVideo;

use App\Repositories\MessageRepository;
use App\Repositories\VideoRepository;

use App\UserVideo;

class VideoController extends Controller
{
    /**
     * Upload video file
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function upload() {
        $user = request()->user();
        $fileKey = request()->hasFile('file')
            ? 'file'
            : 'video';

        $validator = Validator::make(request()->all(), [
            $fileKey => 'required|mimes:3gp,mp4,mpeg,mpg,mov,qt,flv,avi,wmv,webm,m4v',
            'hash' => 'required|string|max:128'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ])->setStatusCode(422);
        }

        try {
            $file = request()->file($fileKey);
            $video = (new UserVideo())->upload(
                $user,
                $file,
                request()->get('hash')
            );
            $videoFileStat = (new getID3())->analyze($video->getSourceVideoFullPath());

            if ($videoFileStat['playtime_seconds'] < 1) {
                $video->delete();
                return response()->json([
                    'error' => 'uploadVideos.error.lowDuration'
                ], 422);
            }

            if ($videoFileStat['playtime_seconds'] > 900) {
                $video->delete();
                return response()->json([
                    'error' => 'uploadVideos.error.highDuration'
                ], 422);
            }

            if ($video) {
                $isSingleServerMode = config('filesystems.video.single_server_mode', true);
                $queueSuffix = $isSingleServerMode ? '' : "-{$video->storage}";

                dispatch((new ConvertVideo($user, $video, 'mp4'))->onQueue("video-convert-fast$queueSuffix"));
            }

            $video->setUrls(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json($video);
    }

    public function delete($videoId)
    {
        $user = request()->user();
        $video = (new VideoRepository())->findUserVideo($user->id, $videoId);

        if (is_null($video)) {
            return response()->json("Video doesn't exist", 422);
        }

        // delete video from messages
        (new MessageRepository(null))->detachVideo($video->id);

        $video->delete();

        return response()->json('ok');
    }

    public function getVideoProcess($hash) {
        $user = request()->user();

        try {
            $video = UserVideo::where('user_id', $user->id)
                ->where('hash', $hash)
                ->first();

            return response()->json([
                'user_id' => $video->user_id,
                'hash' => $video->hash,
                'percentage' => $video->percentage,
                'status' => $video->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
