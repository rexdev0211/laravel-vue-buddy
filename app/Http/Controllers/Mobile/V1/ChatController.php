<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Services\Timer;
use App\Event;
use App\EventMembership;
use App\Events\CheckMessage;
use App\Events\NewGroupMessageReceived;
use App\Services\ChatService;
use App\Facades\Helper;
use App\UserPhoto;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

use App\Events\NewEventMessageReceived;
use App\Events\NewMessageReceived;
use App\Events\UpdateMessageSent;
use App\Events\UpdateGroupMessage;

use App\Repositories\MessageRepository;
use App\Repositories\PhotoRepository;
use App\Repositories\UserRepository;
use App\Repositories\VideoRepository;
use App\Repositories\EventRepository;

use App\Services\MobileNotificationsService;
use App\Services\SpamService;

use App\User;
use App\Message;

class ChatController extends Controller
{
    /**
     * Get chat dialogs list
     *
     * @return \Illuminate\Http\Response
     */
    public function getConversations()
    {
        $conversations = [
            'all' => [],
            'unread' => [],
            'favorites' => [],
        ];

        $currentUser = request()->user();
        $group = request()->get('group', 'all');
        $page = request()->get('page', 0);

        if (empty($group)) {
            $group = 'all';
        }

        $chatService = new ChatService();
        $chatService->setCurrentUser($currentUser);
        $chatService->setPage($page);
        $chatService->setLimit(request()->get('limit', 20));
        $conversations[$group] = $chatService->getConversations($group);

        if (!$currentUser->seen_welcome_message) {
            $language = !empty($currentUser->language)
                ? $currentUser->language
                : 'en';
            $textMessage = trans('message.hello', [], $language);

            /** @var User $userFrom */
            $userFrom = User::find(config('const.BB_USER_ID'));

            array_unshift($conversations['all'], [
                'chatType' => 'user',
                'interlocutor' => [
                    'id'                => (int) config('const.BB_USER_ID'),
                    'link'              => !empty($userFrom) ? $userFrom->link                  : 'buddy',
                    'name'              => !empty($userFrom) ? $userFrom->name                  : 'BUDDY',
                    'photo_small'       => !empty($userFrom) ? $userFrom->getPhotoUrl('180x180'): UserPhoto::DEFAULT_IMAGE_SMALL,
                    'photo_rating'      => '',
                    'isOnline'          => $userFrom->isOnline(),
                    'wasRecentlyOnline' => $userFrom->wasRecentlyOnline(),
                    'isFavorite'        => $userFrom->isFavorite(),
                ],
                'message' => [
                    'id'        => 'ID',
                    'user_from' => config('const.BB_USER_ID'),
                    'user_to'   => $currentUser->id,
                    'event_id'  => null,
                    'msg_type'  => 'text',
                    'message'   => $textMessage,
                    'image_id'  => null,
                    'video_id'  => null,
                    'channel'   => null,
                    'is_read'   => $currentUser->welcome_message_opened == true
                        ? 'yes'
                        : 'no',
                    'idate'     => Carbon::parse($currentUser->created_at)->toDateTimeString(),
                    'hash'      => null,
                    'cancelled' => false,
                ],
                'unreadMessagesCount' => $currentUser->welcome_message_opened == true ? 0 : 1,
                'platform' => 'mobile'
            ]);
        }

        return response()->json($conversations);
    }

    /**
     * @param int $interlocutorId
     *
     * @return JsonResponse
     */
    public function getMessages(int $interlocutorId): JsonResponse
    {
        $userRepository = new UserRepository();
        $currentUser = request()->user();
        $maxTimestamp = request()->get('maxTimestamp');

        $interlocutor = $userRepository->findUser($interlocutorId);
        if (empty($interlocutor)) {
            return response()->json(['error' => 'User not found']);
        }

        if (
            $interlocutor->isBlockedBy($currentUser)
            ||
            $interlocutor->isSuspended()
        ) {
            return response()->json(['messages' => [], 'user' => $interlocutor]);
        }

        $limit = config('const.LOAD_API_CHAT_MESSAGES_LIMIT');

        $page = request()->get('page', 0);
        $messageService = new ChatService();
        $messageService->setInterlocutor($interlocutor);
        $messageService->setCurrentUser($currentUser);
        $messageService->setPage($page);
        $messageService->setLimit(request()->get('limit', $limit));
        $messageService->setMaxTimestamp($maxTimestamp);
        $messageService->setChannel(Message::CHANNEL_USER);

        if (!empty($maxTimestamp)) {
            // get messages history starting last loaded message
            $messages = $messageService->getMessages();

            //it loads latest messages on first load
        } else {
            // get all unread messages
            $messages = $messageService->getUnreadMessages();

            // if count is smaller than required limit
            if (count($messages) < $limit) {
                // get user latest messages
                $messageService->setMaxTimestamp(null);
                $messages = $messageService->getMessages();
            }

            $cloakModeEnabled =
                $currentUser->isPro()
                &&
                $currentUser->discreet_mode;

            // Mark messages as read after first load
            $messageService->markMessagesAsRead($cloakModeEnabled);
        }

        $messages = $messageService->prepareMessagesCollectionToResponse($messages);

        if ((int) $interlocutor->id == (int) config('const.BB_USER_ID') && $page == 0) {
            $userCreatedAt = Carbon::parse($currentUser->created_at);

            if (empty($maxTimestamp) || $userCreatedAt->greaterThan(Carbon::parse($maxTimestamp))) {
                $language = !empty($currentUser->language) ? $currentUser->language : 'en';
                $textMessage = trans('message.hello', [], $language);

                array_unshift($messages,
                    [
                        'id'        => 'message_id',
                        'user_from' => (int) config('const.BB_USER_ID'),
                        'user_to'   => $currentUser->id,
                        'event_id'  => null,
                        'msg_type'  => 'text',
                        'message'   => $textMessage,
                        'image_id'  => null,
                        'video_id'  => null,
                        'channel'   => 'user',
                        'is_read'   => $currentUser->welcome_message_opened == true ? 'yes': 'no',
                        'idate'     => $userCreatedAt->toDateTimeString(),
                        'hash'      => null,
                        'cancelled' => false,
                    ]
                );

                $currentUser->welcome_message_opened = true;
                $currentUser->save();
            }
        }

        return response()->json([
            'messages' => $messages
        ]);
    }

    /**
     * @param int $eventId
     * @param int $interlocutorId
     *
     * @return JsonResponse
     */
    public function getEventMessages(int $eventId, int $interlocutorId): JsonResponse
    {
        $currentUser = request()->user();
        $maxTimestamp = request()->get('maxTimestamp');

        $event = (new EventRepository())->findEvent($eventId);
        $interlocutor = (new UserRepository())->findWithTrashedUser($interlocutorId);

        if (empty($event)) {
            return response()->json("Event doesn't exist", 422);
        }

        $limit = config('const.LOAD_API_CHAT_MESSAGES_LIMIT');

        $messageService = new ChatService();
        $messageService->setInterlocutor($interlocutor);
        $messageService->setCurrentUser($currentUser);
        $messageService->setEvent($event);
        $messageService->setLimit(request()->get('limit', $limit));
        $messageService->setPage(request()->get('page', 0));
        $messageService->setMaxTimestamp($maxTimestamp);
        $messageService->setChannel(Message::CHANNEL_EVENT);

        //it loads messages history
        if (!empty($maxTimestamp)) {
            // get messages history starting last loaded message
            $messages = $messageService->getMessages();
            // it loads latest messages on first load
        } else {
            // get all unread messages
            $messages = $messageService->getUnreadMessages();

            //if count is smaller than required limit
            if (count($messages) < $limit) {
                // get user latest messages
                $messageService->setMaxTimestamp(null);
                $messages = $messageService->getMessages();
            }

            // Mark messages as read after first load
            $messageService->markMessagesAsRead();
        }

        $messages = $messageService->prepareMessagesCollectionToResponse($messages);

        return response()->json([
            'messages' => $messages
        ]);
    }

    /**
     * @param int $eventId
     *
     * @return JsonResponse
     */
    public function getGroupMessages(int $eventId): JsonResponse
    {
        $maxTimestamp = request()->input('maxTimestamp', 0);

        Timer::start('event-group-messages-find-event:' . $eventId);
        $event = (new EventRepository())->findEvent($eventId);
        if (empty($event)) {
            return response()->json(['error' => "Event doesn't exist"], 422);
        }
        Timer::end('event-group-messages-find-event:' . $eventId);

        Timer::start('event-group-messages-memberchip-check:' . $eventId);
        $membership = EventMembership::search(request()->user(), $event);
        if (
            empty($membership)
            ||
            !in_array($membership->status, [
                EventMembership::STATUS_MEMBER,
                EventMembership::STATUS_HOST,
            ])
        ) {
            return response()->json([
                'error' => "You're not a member of this event"
            ], 403);
        }
        Timer::end('event-group-messages-memberchip-check:' . $eventId);

        $limit = config('const.LOAD_API_CHAT_MESSAGES_LIMIT');

        Timer::start('event-group-messages-get-messages:' . $eventId);
        $messageService = new ChatService();
        $messageService->setEvent($event);
        $messageService->setLimit(request()->input('limit', $limit));
        $messageService->setPage(request()->input('page', 0));
        $messageService->setMaxTimestamp($maxTimestamp);
        $messageService->setChannel(Message::CHANNEL_GROUP);
        $messageService->setCurrentUser(auth()->user());

        $messages = $messageService->getMessages();
        Timer::end('event-group-messages-get-messages:' . $eventId);

        Timer::start('event-group-messages-prepare-collection:' . $eventId);
        $messages = $messageService->prepareMessagesCollectionToResponse($messages);
        Timer::end('event-group-messages-prepare-collection:' . $eventId);

        Timer::start('event-group-messages-mark-as-read:' . $eventId);
        $messageService->markMessagesAsRead();
        Timer::end('event-group-messages-mark-as-read:' . $eventId);

        return response()->json([
            'messages' => $messages
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function sendMessage(): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = request()->user();
        $userRepository = new UserRepository();
        $messageRepository = new MessageRepository();

        $data = request()->all();
        $validator = Validator::make($data, [
            'userId' => 'required_unless:channel,group|integer',
            'eventId' => 'required_unless:channel,user',
            'message' => 'required|string',
            'msgType' => 'required|string|in:text,location',
            'channel' => 'required|string|in:user,event,group'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        $spamService = new SpamService;
        $spamService->setUser($currentUser);
        $spamService->setContent($data['message']);
        $ghosted   = $spamService->userGhostAttempt();
        $suspended = $spamService->userSuspendAttempt();
        if ($suspended) {
            return response()->json([
                'error' => 'Such message is not allowed'
            ], 422);
        }

        $interlocutor = null;
        $userMessage = $data['message'];
        $interlocutorId = (int)($data['userId'] ?? null);
        $eventId = (int)($data['eventId'] ?? null);
        $channel = $data['channel'];
        $messageType = $data['msgType'];

        if (!empty($eventId)) {
            $event = (new EventRepository())->findEvent($eventId);
        } else {
            $event = null;
        }

        if (!empty($interlocutorId)) {
            $interlocutor = $userRepository->findUser($interlocutorId);
        }

        if (
            !$currentUser->isGhosted()
            &&
            !empty($interlocutor)
            &&
            $interlocutor->isBlockedBy($currentUser)
        ) {
            return response()->json([
                'error' => 'you_cant_send_messages_to_this_user'
            ], 422);
        }

        $data = [
            'user_from' => $currentUser->id,
            'message' => $userMessage,
            'msg_type' => $messageType,
            'channel' => $channel,
            'is_sender_ghosted' => $currentUser->isGhosted() ? 1 : 0,
            'hash' => request()->get('hash') ?: null,
        ];
        if ($channel != Message::CHANNEL_GROUP) {
            $data['user_to'] = $interlocutorId;
            $data['is_recipient_ghosted'] = $interlocutor->isGhosted() ? 1 : 0;
        }
        if ($channel != Message::CHANNEL_USER) {
            $data['event_id'] = $eventId;
        }
        $message = $messageRepository->createMessage($data);

        if ($channel == Message::CHANNEL_USER) {
            $spamService = new SpamService;
            $spamService->setUser($currentUser);
            $spamService->userGhostAttempt();
            $currentUser->fresh();
        }

        if (!empty($interlocutor)) {
            if (
                $channel == Message::CHANNEL_USER
                ||
                $channel == Message::CHANNEL_EVENT
                and
                !empty($interlocutor)
                and
                !$currentUser->isGhosted()
                ||
                $interlocutor->isGhosted()
            ) {
                (new MobileNotificationsService())->newMessage($message, [$interlocutor]);
                $userRepository->updateUser($interlocutorId, ['has_new_messages' => true]);

                $conversationBroadcasted = null;
                if ($channel == Message::CHANNEL_USER) {
                    $conversationBroadcasted = ChatService::getConversationGeneralAttributes($currentUser, $interlocutor, $message, 0);
                    event(new NewMessageReceived($conversationBroadcasted));
                } elseif ($channel == Message::CHANNEL_EVENT) {
                    $conversationBroadcasted = ChatService::getEventConversationGeneralAttributes($currentUser, $interlocutor, $event, $message, 0);
                    event(new NewEventMessageReceived($conversationBroadcasted));
                }
            }
        }

        if (
            $channel == Message::CHANNEL_GROUP
            and
            !$currentUser->isGhosted()
        ) {
            $recipientsQuery = User::whereIn(
                'id',
                $event->activeMembers->map(function($user){
                    return $user->id;
                })->toArray()
            )
                ->where('id', '!=', $currentUser->id);

            $recipientsQuery->update(['has_new_messages' => true]);

            $conversationData = ChatService::getGroupConversationGeneralAttributes($currentUser, $event, $message, 0);
            $broadcastingData = ['ignore_recipient_id' => [$currentUser->id]];
            event(new NewGroupMessageReceived($conversationData, $broadcastingData));
            (new MobileNotificationsService())->newMessage($message, $recipientsQuery->get()->all());
        }

        $conversationResponse = (new MessageRepository())->getConversationAndClearCache($interlocutor, $currentUser, $message, $channel, $event, $eventId);

        if ($interlocutor->id ?? null == config('const.BB_USER_ID')) {
            $currentUser->seen_welcome_message = true;
            $currentUser->save();
        }

        return response()->json([
            'conversation' => $conversationResponse
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function sendMessages(): JsonResponse
    {
        $currentUser    = request()->user();
        $userRepository = new UserRepository();

        $request = request()->all();
        $rules   = [
            'userId'  => 'required_unless:channel,group|integer',
            'eventId' => 'required_unless:channel,user',
            'msgType' => 'required|string|in:photo,video',
            'channel' => 'required|string|in:user,event,group'
        ];

        if (!empty($request['msgType'])) {
            if ($request['msgType'] == 'photo') {
                $rules += [
                    'photosIds' => 'required_if:msgType,photo|array|min:1',
                    'photosIds.*' => 'required|integer',
                ];
            } elseif ($request['msgType'] == 'video') {
                $rules += [
                    'videosIds' => 'required_if:msgType,video|array|min:1',
                    'videosIds.*' => 'required|integer',
                ];
            }
        }

        $validator = Validator::make($request, $rules);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        $spamService = new SpamService;
        $spamService->setUser($currentUser);
        $suspended = $spamService->userSuspendAttempt();
        $ghosted = $spamService->userGhostAttempt();

        if ($suspended) {
            return response()->json([
                'error' => 'Such message is not allowed'
            ], 422);
        }

        $interlocutor   = null;
        $interlocutorId = (int)($request['userId'] ?? null);
        $eventId        = (int)($request['eventId'] ?? null);
        $channel        = $request['channel'];
        $messageType    = $request['msgType'];

        if (!empty($eventId)) {
            $event = (new EventRepository())->findEvent($eventId);
        } else {
            $event = null;
        }

        if (!empty($interlocutorId)) {
            $interlocutor = $userRepository->findUser($interlocutorId);
        }

        if (!$currentUser->isGhosted() && !empty($interlocutor) && $interlocutor->isBlockedBy($currentUser)) {
            return response()->json([
                'error' => 'you_cant_send_messages_to_this_user'
            ], 422);
        }

        $photoRepository     = new PhotoRepository();
        $videoRepository     = new VideoRepository();
        $messageRepository   = new MessageRepository();

        $allowNotifications  = !$currentUser->isGhosted() || (!empty($interlocutor) && $interlocutor->isGhosted());
        $notificationService = new MobileNotificationsService();
        $mediaIds            = $request[$messageType == 'photo' ? 'photosIds': 'videosIds'] ?? [];
        $messages            = [];
        $activeMembers       = []; 

        if ($channel == Message::CHANNEL_GROUP) {
            $activeMembers = $event->activeMembers->pluck('id')->toArray() ?? [];

            $recipients = User::whereIn('id', $activeMembers)
                              ->where('id', '<>', $currentUser->id)
                              ->get()
                              ->all();
        }

        foreach ($mediaIds as $mediaId) {
            if ($messageType == 'photo') {
                $media = $photoRepository->findUserPhoto($currentUser->id, $mediaId);
            } elseif ($messageType == Message::TYPE_VIDEO) {
                $media = $videoRepository->findUserVideo($currentUser->id, $mediaId);
            }

            if (!empty($media)) {
                $data = [
                    'user_from'         => $currentUser->id,
                    'msg_type'          => $messageType == 'photo' ? Message::TYPE_IMAGE : Message::TYPE_VIDEO,
                    'channel'           => $channel,
                    'is_sender_ghosted' => $currentUser->isGhosted() ? 1 : 0
                ];

                if ($channel != Message::CHANNEL_GROUP) {
                    $data['user_to'] = $interlocutorId;
                    $data['is_recipient_ghosted'] = $interlocutor->isGhosted() ? 1 : 0;
                }

                if ($channel != Message::CHANNEL_USER) {
                    $data['event_id'] = $eventId;
                }

                if ($messageType == 'photo') {
                    $data['image_id'] = (int) $mediaId;
                    $data['hash']     = $request['hashes'][$data['image_id']] ?? null;
                } elseif ($messageType == Message::TYPE_VIDEO) {
                    $data['video_id'] = (int) $mediaId;
                    $data['hash']     = $request['hashes'][$data['video_id']] ?? null;
                }

                $message = $messageRepository->createMessage($data);

                /* Broadcasting */
                if (!$currentUser->isGhosted()) {
                    if ($channel == Message::CHANNEL_USER) {
                        $conversationBroadcasted = ChatService::getConversationGeneralAttributes($currentUser, $interlocutor, $message, 0);

                        event(new NewMessageReceived($conversationBroadcasted));
                        event(new CheckMessage($conversationBroadcasted, $currentUser->id));

                    } elseif ($channel == Message::CHANNEL_EVENT) {
                        $conversationBroadcasted = ChatService::getEventConversationGeneralAttributes($currentUser, $interlocutor, $event, $message, 0);

                        event(new NewEventMessageReceived($conversationBroadcasted));
                        event(new CheckMessage($conversationBroadcasted, $currentUser->id, $eventId));
                    } elseif ($channel == Message::CHANNEL_GROUP) {
                        $conversationData = ChatService::getGroupConversationGeneralAttributes(null, $event, $message, 0);

                        event(new NewGroupMessageReceived($conversationData, ['ignore_recipient_id' => [$currentUser->id]]));
                        event(new CheckMessage($conversationData, $currentUser->id, $eventId));

                        if ($allowNotifications) {
                            $notificationService->newMessage($message, $recipients);
                        }
                    }
                }

                if ($allowNotifications && !empty($interlocutor)) {
                    $notificationService->newMessage($message, [$interlocutor]);
                }

                $messages[] = $message->getGeneralAttributes();
            }
        }

        if ($allowNotifications && !empty($messages)) {
            if ($channel != Message::CHANNEL_GROUP) {
                $userRepository->updateUser($interlocutorId, ['has_new_messages' => true]);
            } else {
                User::whereIn('id', $activeMembers)
                    ->where('id', '<>', $currentUser->id)
                    ->update(['has_new_messages' => true]);
            }
        }

        return response()->json([
            'conversation' => (new MessageRepository())->getConversationAndClearCache($interlocutor, $currentUser, $message, $channel, $event, $eventId),
            'messages'     => $messages
        ]);
    }

    /**
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function markConversationAsRead(int $userId): JsonResponse
    {
        $currentUser = request()->user();
        $interlocutor = (new UserRepository())->findUser($userId);
        if (empty($interlocutor)) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        $cloakModeEnabled =
            $currentUser->isPro()
            &&
            $currentUser->discreet_mode;

        $messageService = new ChatService();
        $messageService->setInterlocutor($interlocutor);
        $messageService->setCurrentUser($currentUser);
        $messageService->setChannel(Message::CHANNEL_USER);
        $messageService->markMessagesAsRead($cloakModeEnabled);

        return response()->json('ok');
    }

    /**
     * @param int $eventId
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function markEventConversationAsRead(int $eventId, int $userId): JsonResponse
    {
        $currentUser = request()->user();
        $interlocutor = (new UserRepository())->findUser($userId);
        if (empty($interlocutor)) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        $event = (new EventRepository())->findEvent($eventId);
        if (empty($event)) {
            return response()->json([
                'error' => 'Event not found'
            ], 404);
        }

        $messageService = new ChatService();
        $messageService->setInterlocutor($interlocutor);
        $messageService->setCurrentUser($currentUser);
        $messageService->setEvent($event);
        $messageService->setChannel($event->type == $event::TYPE_BANG ? Message::CHANNEL_GROUP : Message::CHANNEL_EVENT);
        $messageService->markMessagesAsRead(false);

        return response()->json('ok');
    }

    /**
     * @return JsonResponse
     */
    public function markMessageAsRemoved(): JsonResponse
    {
        $data = request()->all();
        $validator = Validator::make($data, [
            'messageId' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        $messageId = $data['messageId'];
        if (empty($messageId)) {
            return response()->json(['error' => 'Not found a message to delete'], 422);
        }

        $currentUser = request()->user();
        $result = (new MessageRepository())->markMessageAsCancelled($messageId, $currentUser->id);
        if (!empty($result['cancelled'])) {
            if ($result['message']['channel'] == Message::CHANNEL_GROUP) {
                event(new UpdateGroupMessage($result['message']));
            } else {
                event(new UpdateMessageSent($result['message']));
            }
        }

        return response()->json($result);
    }

    /**
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function removeConversation(int $userId): JsonResponse
    {
        $currentUser = request()->user();

        if ($userId == config('const.BB_USER_ID')) {
            $currentUser->seen_welcome_message = true;
            $currentUser->save();
        }

        (new MessageRepository())->removeConversation(Message::CHANNEL_USER, $currentUser->id, $userId, null);
        (new ChatService())->removeCacheConversation(Message::CHANNEL_USER, $currentUser->id, $userId, null);
        return response()->json('ok');
    }

    /**
     * @param int $eventId
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function removeEventConversation(int $eventId, int $userId): JsonResponse
    {
        $currentUser = request()->user();
        (new MessageRepository())->removeConversation(Message::CHANNEL_EVENT, $currentUser->id, $userId, $eventId);
        (new ChatService())->removeCacheConversation(Message::CHANNEL_EVENT, $currentUser->id, $userId, $eventId);
        return response()->json('ok');
    }

    /**
     * @param int $interlocutorId
     *
     * @return JsonResponse
     */
    public function getChatImagesList(int $interlocutorId): JsonResponse
    {
        $currentUser = request()->user();
        $messages = (new MessageRepository())->getImages(Message::CHANNEL_USER, $currentUser->id, $interlocutorId, null);
        return response()->json($messages);
    }

    /**
     * @param int $interlocutorId
     *
     * @return JsonResponse
     */
    public function getChatVideosList(int $interlocutorId): JsonResponse
    {
        $currentUser = request()->user();
        $messages = (new MessageRepository())->getVideos(Message::CHANNEL_USER, $currentUser->id, $interlocutorId, null);
        return response()->json($messages);
    }

    /**
     * @param int $eventId
     * @param int $interlocutorId
     *
     * @return JsonResponse
     */
    public function getEventChatImagesList(int $eventId, int $interlocutorId): JsonResponse
    {
        $currentUser = request()->user();
        $messages = (new MessageRepository())->getImages(Message::CHANNEL_EVENT, $currentUser->id, $interlocutorId, $eventId);
        return response()->json($messages);
    }

    /**
     * @param int $eventId
     * @param int $interlocutorId
     *
     * @return JsonResponse
     */
    public function getEventChatVideosList(int $eventId, int $interlocutorId): JsonResponse
    {
        $currentUser = request()->user();
        $messages = (new MessageRepository())->getVideos(Message::CHANNEL_EVENT, $currentUser->id, $interlocutorId, $eventId);
        return response()->json($messages);
    }

    /**
     * @param int $eventId
     *
     * @return JsonResponse
     */
    public function getGroupEventChatImagesList(int $eventId): JsonResponse
    {
        $currentUser = request()->user();
        $event = Event::find($eventId);
        if (!EventMembership::isActiveMember($currentUser, $event)) {
            return response()->json(['error' => 'You haven\'t access to this chat'], 403);
        }

        $messages = (new MessageRepository())->getImages(Message::CHANNEL_GROUP, null, null, $eventId);
        return response()->json($messages);
    }

    /**
     * @param int $eventId
     *
     * @return JsonResponse
     */
    public function getGroupEventChatVideosList(int $eventId): JsonResponse
    {
        $currentUser = request()->user();
        $event = Event::find($eventId);
        if (!EventMembership::isActiveMember($currentUser, $event)) {
            return response()->json(['error' => 'You haven\'t access to this chat'], 403);
        }

        $messages = (new MessageRepository())->getVideos(Message::CHANNEL_GROUP, null, null, $eventId);
        return response()->json($messages);
    }
}
