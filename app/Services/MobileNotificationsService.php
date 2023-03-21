<?php

namespace App\Services;

use App\Event;
use App\Facades\Helper;
use App\Message;
use App\Notification;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use \Log;

class MobileNotificationsService
{
    /**
     * @param mixed $message
     * @param array $recipients
     *
     * @return void
     */
    public function newMessage($message, array $recipients): void
    {
        $recipients = $this->filterRecipients($recipients);
        if (empty($recipients)) {
            return;
        }

        $content = null;
        switch ($message->msg_type) {
            case Message::TYPE_TEXT:{
                $content = Str::limit($message->message, 50);
                break;
            }
            case Message::TYPE_IMAGE:{
                $content = 'Sent an image';
                break;
            }
            case Message::TYPE_VIDEO:{
                $content = 'Sent a video';
                break;
            }
            case Message::TYPE_LOCATION:{
                $content = 'Shared a location';
                break;
            }
            default:{
                $content = 'Sent a new message';
                break;
            }
        }

        /** @var User $sender */
        $sender = $message->userFrom;
        $recipientIds = collect($recipients)->map(function(User $user){
            return $user->id;
        })->toArray();

//        $playerIds = collect($recipients)->reduce(function(array $result, User $user){
//            $userPlayerIds = $user->onesignalPlayers
//                ->pluck('player_id')
//                ->toArray();
//            return array_merge($result, $userPlayerIds);
//        }, []);
        $playerIds = [];

        Helper::toggleAdultContent(false);
        if (!empty($message->event)) {
            /** @var Event $event */
            $event = $message->event;
            $payload = [
                'large_icon' => $event->getPhotoUrl('180x180'),
                'headings'   => ['en' => "📅  {$event->title}"],
                'data'       => ['type' => 'message', 'source' => $event->type == $event::TYPE_BANG ? 'group' : 'event', 'user_id' => $message->userFrom->id, 'event_id' => $event->id]
            ];

            $payload['contents'] = $event->type == $event::TYPE_BANG
                ? ['en' => "{$message->userFrom->name}: " . $content]
                : ['en' => $content];
        } else {
            $payload = [
                'large_icon' => $sender->getPhotoUrl('180x180'),
                'headings'   => ['en' => $message->userFrom->name],
                'contents'   => ['en' => $content],
                'data'       => ['type' => 'message', 'source' => 'user', 'user_id' => $message->userFrom->id]
            ];
        }
        Helper::toggleAdultContent(true);

        foreach ($recipientIds as $key => $value) {
            $recipientIds[$key] = (string) $value;
        }

        $payload['include_external_user_ids'] = array_values($recipientIds);
        $payload['include_player_ids']        = array_values($playerIds);

        $this->sendRequest($payload);
    }

    /**
     * @param Notification $notification
     * @param array $recipients
     *
     * @return void
     */
    public function newNotification(Notification $notification, array $recipients): void
    {
        $recipients = $this->filterRecipients($recipients);
        if (empty($recipients)) {
            return;
        }

        /** @var User $sender */
        $sender = $notification->userFrom;

        $content = null;
        $largeIcon = null;
        $data = null;
        Helper::toggleAdultContent(false);
        switch ($notification->type) {
            case 'wave':{
                $headings  = ['en' => "New tap"];
                $content   = ['en' => "👋  {$sender->name} waved to you"];
                $largeIcon = asset("/assets/img/icons/taps/{$notification->sub_type}.png");
                $data      = ['type' => 'wave'];
                break;
            }
            case 'event':{
                $event = $notification->notificationEvent ?? null;
                if (!empty($event)) {
                    $headings  = ['en' => "New like"];
                    $content   = ['en' => "🖤  {$sender->name} liked you event"];
                    $largeIcon = $sender->getPhotoUrl('180x180');
                    $data      = ['type' => 'like'];
                } else {
                    return;
                }
                break;
            }
        }
        Helper::toggleAdultContent(true);

        $recipientIds = collect($recipients)->map(function(User $user){
            return $user->id;
        })->toArray();

//        $playerIds = collect($recipients)->reduce(function (array $result, User $user) {
//            $userPlayerIds = $user->onesignalPlayers
//                ->pluck('player_id')
//                ->toArray();
//            return array_merge($result, $userPlayerIds);
//        }, []);

        $playerIds = [];

        foreach ($recipientIds as $key => $value) {
            $recipientIds[$key] = (string) $value;
        }

        $payload = [
            'large_icon'                => $largeIcon,
            'headings'                  => $headings,
            'contents'                  => $content,
            'include_external_user_ids' => array_values($recipientIds),
            'include_player_ids'        => array_values($playerIds),
            'data'                      => $data
        ];

        $this->sendRequest($payload);
    }

    /**
     * @param array $recipients
     *
     * @return array
     */
    protected function filterRecipients(array $recipients): array
    {
        $recipients = array_filter($recipients);

        // Last activity max timestamp
        $diffSeconds = 0 ?? config('const.REFRESH_LAST_ACTIVE_SECONDS') + 30;
        $lastActiveLatest = Carbon::now()->subSeconds($diffSeconds);

        // Filter recipients
        $recipients = array_filter($recipients, function(User $user) use ($lastActiveLatest){
            return
                $user->pushNotificationsEnabled()
                /*&&
                $lastActiveLatest->gt($user->last_active)*/;
        });

        return $recipients;
    }

    /**
     * @param array $payload
     */
    protected function sendRequest(array $payload): void
    {
        $fields = [
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1
        ] + $payload;

        Log::channel('onesignal')->debug('OneSignal request', ['request' => $fields]);
        $response = \OneSignal::sendPush($fields);
        Log::channel('onesignal')->debug('OneSignal response', ['response' => $response]);
    }
}
