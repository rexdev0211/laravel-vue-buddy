<?php

namespace App\Http\Controllers\Web\Admin;

use App\EventMembership;
use App\Events\EventMembershipUpdated;
use App\Services\ChatService;
use App\User;
use App\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Web\Controller;

class TestsController extends Controller
{
    /**
     * @param Request $request
     * @param string $userId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateConversation(Request $request, string $userId)
    {
        return response('event launched');
    }

    public function checkUserLatestMessages($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return 'User is not found';
        }

        $date = new \DateTime('yesterday');

        $countRedis = Redis::get("msg-limit-24hrs:{$user->id}");

        $countDB = Message::where('user_from', $user->id)
                          ->where('idate', '>', $date)
                          ->pluck('user_to')
                          ->unique()
                          ->count();

        $countMessages = $messages = Message::where('user_from', $user->id)
                                            ->where('idate', '>', $date)
                                            ->count();

        echo 'Date: '. $date->format('Y-m-d') .'<br />';
        echo 'Count in redis: '. $countRedis .'<br />';
        echo 'Count in DB: '. $countDB .'<br />';
        echo 'Total messages: '.$countMessages.'<br />';

        if (request()->get('detailed')) {
            $usersTo = Message::where('user_from', $user->id)
                              ->where('idate', '>', $date)
                              ->pluck('user_to')
                              ->unique();

            $messages = Message::where('user_from', $user->id)
                              ->where('idate', '>', $date)
                              ->get();

            dd($usersTo, $messages);
        }

        dd('Done');
    }

    public function clearUserLatestMessagesCache()
    {
        /* Get all users */
        $users = User::select('id')
                     ->get()
                     ->pluck('id');

        /* Clear counter for messages if it's set */
        foreach ($users as $userId) {
            if (request()->get('dd')) {
                dd($userId);
            }

            if (Redis::get("msg-limit-24hrs:{$userId}")) {
                Redis::set("msg-limit-24hrs:{$userId}", null);
                echo 'Cache cleared for user #'.$userId.'<br />';
            } else {
                echo 'User #'.$userId.' has no data in cache <br />';
            }
            if (request()->get('dd2')) {
                dd($userId);
            }
        }
        dd('Done');
    }

    public function checkAppleSettings()
    {
        dd((new \App\Services\Payments\ApplePaymentService)->testSettingsData());
    }

    public function showUserConversations($userId)
    {
        $limit = request()->get('limit', 20);
        $page  = request()->get('page', 0);

        $blockedUserIds = \App\UserBlocked::select('user_blocked_id')
                                     ->where('user_id', $userId)
                                     ->groupBy('user_blocked_id')
                                     ->get()
                                     ->pluck('user_blocked_id')
                                     ->toArray();

        $blockerUserIds = \App\UserBlocked::select('user_id')
                                     ->where('user_blocked_id', $userId)
                                     ->groupBy('user_id')
                                     ->get()
                                     ->pluck('user_id')
                                     ->toArray();

        $blockUserIds = array_values(array_unique(array_merge($blockerUserIds, $blockedUserIds)));

        if (request()->get('dd')) {
            dd($userId, $blockUserIds);
        }

        dd($userId, $blockUserIds, (new \App\Repositories\MessageRepository)->getConversationMessagesAll($userId, $limit, $limit*$page));
    }

    public function showConfig()
    {
        $config = request()->get('config') ?: 'app';

        dd(config($config));
    }
}
