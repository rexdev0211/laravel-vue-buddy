<?php

namespace App\Http\Controllers\Web\Admin;

use App\User;
use App\UserVideo;
use App\Services\BackendService;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\Controller;

class VideoServerController extends Controller
{
    /**
     * @param BackendService $backendService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(BackendService $backendService, Request $request)
    {
        $log = '';

        if ($request->exists('remove-originals')) {
            $log = $backendService->removeOriginalVideos();
        }

        $total = UserVideo::count();

        $deletedUsers = User::whereNotNull('deleted_at')
                            ->count();

        $deletedUsersVideos = UserVideo::with('user')
                                       ->whereHas('user', function ($query) {
                                           $query->whereNotNull('deleted_at');
                                       })
                                       ->count();

        return view('admin.videoServer.index', [
            'freeSpace'    => $backendService->getVideosServerFreeSpace(),
            'log'          => $log,
            'total'        => $total,
            'deletedUsers' => [
                'count'  => $deletedUsers,
                'videos' => $deletedUsersVideos,
            ],
        ]);
    }

    /**
     * Clear videos for deleted users
     */
    public function clearDeleted(BackendService $backendService)
    {
        $limit = 10;
        $deleteVideos = UserVideo::with('user')
                                 ->whereHas('user', function ($query) {
                                     $query->whereNotNull('deleted_at');
                                 })
                                 ->limit($limit)
                                 ->get();

        $log = '';
        foreach ($deleteVideos as $video) {
            $video->delete();
        }

        $total = UserVideo::count();

        $deletedUsersVideos = UserVideo::with('user')
                                       ->whereHas('user', function ($query) {
                                           $query->whereNotNull('deleted_at');
                                       })
                                       ->count();

        return response()->json([
            'freeSpace'    => $backendService->getVideosServerFreeSpace(),
            'total'        => number_format($total, 0, '.', ' '),
            'log'          => 'Removed '. $deleteVideos->count() .' videos.'. $log .'<br />',
            'deletedUsers' => [
                'queued' => $deletedUsersVideos,
                'videos' => number_format($deletedUsersVideos, 0, '.', ' '),
            ],
        ]);
    }
}
