<?php

namespace App\Services;

use App\EventMembership;
use App\Events\UpdateMessageReceived;
use App\Facades\Helper;
use App\Jobs\CreateConversationsCache;
use App\Models\Event\EventMessagesRead;
use App\Message;
use App\Repositories\MessageRepository;
use App\Repositories\UserFavoriteRepository;
use App\UserPhoto;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Jobs\CheckConversationsJob;

use App\Event;
use App\User;
use Illuminate\Support\Facades\Redis;

class ChatService
{
    /** @var User|null */
    protected $interlocutor;

    /** @var User|null */
    protected $currentUser;

    /** @var Event|null */
    protected $event;

    /** @var string */
    protected $channel;

    /** @var int|null */
    protected $limit;

    /** @var int|null */
    protected $page;

    /** @var string|null */
    protected $maxTimestamp;

    /**
     * @param string $group
     *
     * @return array
     */
    public function getConversations(string $group): array
    {
        $user = $this->getCurrentUser();
        $page = $this->getPage();
        $limit = $this->getLimit() ?? config('const.LOAD_CHAT_WINDOWS_LIMIT');
        $offset = ($page ?? 0) * $limit;

        // Get last messages by user_id
        $messages = collect([]);
        $messageRepository = new MessageRepository();

        $cachedConversations = $messageRepository->getCachedConversations($user->id, (int) $page, $group, $limit);

        if (!$cachedConversations->isEmpty()) {
            $cachedConversations = $cachedConversations->map(function ($message) use ($user) {
                if ($message['chatType'] === Message::CHANNEL_USER || $message['chatType'] === Message::CHANNEL_EVENT) {
                    $interlocutor = User::where('id', $message['interlocutor']['id'])->first();

                    if (is_null($interlocutor)) {
                        return $message;
                    }

                    /**@var User $interlocutor */
                    $message['interlocutor'] = $interlocutor->getAttributesByMode(User::ATTRIBUTES_MODE_CONVERSATION, $user);
                }

                if ($message['chatType'] === Message::CHANNEL_EVENT || $message['chatType'] === Message::CHANNEL_GROUP) {
                    $event = Event::where('id', $message['event']['id'])->first();

                    if (is_null($event)) {
                        return $message;
                    }

                    /**@var Event $event */
                    $message['event'] = $event->getAttributesByMode(Event::ATTRIBUTES_MODE_GENERAL, $user);
                }

                return $message;
            })->toArray();

            if (!empty($user->last_conversation_check)) {
                $lastConversationCheck = Carbon::parse($user->last_conversation_check);

                if ($lastConversationCheck->diffInHours() > env('CONVERSATION_CHECK_INTERVAL', 12)) {
                    CheckConversationsJob::dispatch($user)->delay(0);
                }
            }

            return array_values($cachedConversations);
        }

        switch ($group) {
            case 'all':{
                $messages = $messageRepository->getConversationMessagesAll($user->id, $limit, $offset, $user);
                break;
            }
            case 'unread':{
                $messages = $messageRepository->getConversationMessagesUnread($user->id, $limit, $offset);
                break;
            }
            case 'favorites':{
                $favoriteUserIds = (new UserFavoriteRepository)->getAllFavoriteIds($user->id);
                $messages = $messageRepository->getConversationMessagesFavorites($favoriteUserIds, $user->id, $limit, $offset, $user);
                break;
            }
        }

        $conversationsIds = $messages->pluck('conversation')->toArray();
        $eventsIds = $messages->pluck('event_id')
                              ->unique()
                              ->values()
                              ->filter()
                              ->toArray();

        $conversationsCount = $messageRepository->getUnreadMessagesCountChats($conversationsIds, $user, $eventsIds);

        $conversations = $messages->map(function (Message $entry) use ($user, $messageRepository, $conversationsCount) {
            if ($entry->channel == Message::CHANNEL_USER || $entry->channel == Message::CHANNEL_EVENT) {
                if ($entry->user_from == $user->id) {
                    $interlocutor = $entry->userTo;
                } else {
                    $interlocutor = $entry->userFrom;
                }

                /** @var User $interlocutor */
                if ($interlocutor) {
                    if ($entry->channel == Message::CHANNEL_USER) {
                        $unreadMessagesCount = collect($conversationsCount)
                                               ->where('conversation', $entry->conversation)
                                               ->count();

                        return self::getConversationGeneralAttributes($interlocutor, $user, $entry, $unreadMessagesCount);
                    } else {
                        $unreadMessagesCount = collect($conversationsCount)
                                               ->where('conversation', $entry->conversation)
                                               ->count();

                        return self::getEventConversationGeneralAttributes($interlocutor, $user, $entry->event, $entry, $unreadMessagesCount);
                    }
                }
            } elseif ($entry->channel == Message::CHANNEL_GROUP) {
                $unreadMessagesCount = collect($conversationsCount)
                                       ->where('conversation', $entry->conversation)
                                       ->count();

                return self::getGroupConversationGeneralAttributes($user, $entry->event, $entry, $unreadMessagesCount);
            }
        })
        ->filter(function ($conversation) use($user) {
            if (!empty($conversation->event_id)) {
                $event = Event::find($conversation->event_id);

                if (null != $event) {
                    $membership = EventMembership::search($user, $event);
                    $eventUserId = $event->user_id;
                    $eventUser = User::find($eventUserId);

                    if (empty($membership) ||
                        !in_array($membership->status, [
                            EventMembership::STATUS_MEMBER,
                            EventMembership::STATUS_HOST,
                        ]) ||
                        ($eventUserId != $user->id && $user->isBlockedBy($eventUser))
                    ) {
                        return false;
                    }
                }
            }

            if (!Helper::isApp() && preg_match_all('/Ahoy Buddy/im', ($conversation['message']['message'] ?? '')) && auth()->user()->id == env('BB_USER_ID', 100001)) {
                return false;
            }
            return $conversation ? true : false;
        })
        ->values()
        ->toArray();

        $this->createConversationsCache($user->id, $conversations);

        dispatch(new CreateConversationsCache($user->id))->onQueue('createConversationsCache');

        return $conversations;
    }

    /**
     * @return array
     */
    public function getConversationsForCache(): array
    {
        $user = $this->getCurrentUser();
        $page = $this->getPage();
        $limit = $this->getLimit() ?? config('const.LOAD_CHAT_WINDOWS_LIMIT');
        $offset = ($page ?? 0) * $limit;

        // Get last messages by user_id
        $messageRepository = new MessageRepository();

        $messages = $messageRepository->getConversationMessagesAll($user->id, $limit, $offset, $user);

        $conversationsIds = $messages->pluck('conversation')->toArray();

        $eventsIds = $messages->pluck('event_id')
            ->unique()
            ->values()
            ->filter()
            ->toArray();

        $conversationsCount = $messageRepository->getUnreadMessagesCountChats($conversationsIds, $user, $eventsIds);

        return $messages->map(function (Message $entry) use ($user, $messageRepository, $conversationsCount) {
            if ($entry->channel == Message::CHANNEL_USER || $entry->channel == Message::CHANNEL_EVENT) {
                if ($entry->user_from == $user->id) {
                    $interlocutor = $entry->userTo;
                } else {
                    $interlocutor = $entry->userFrom;
                }

                /** @var User $interlocutor */
                if ($interlocutor) {
                    if ($entry->channel == Message::CHANNEL_USER) {
                        $unreadMessagesCount = collect($conversationsCount)
                            ->where('conversation', $entry->conversation)
                            ->count();

                        return self::getConversationGeneralAttributes($interlocutor, $user, $entry, $unreadMessagesCount);
                    } else {
                        $unreadMessagesCount = collect($conversationsCount)
                            ->where('conversation', $entry->conversation)
                            ->count();

                        return self::getEventConversationGeneralAttributes($interlocutor, $user, $entry->event, $entry, $unreadMessagesCount);
                    }
                }
            } elseif ($entry->channel == Message::CHANNEL_GROUP) {
                $unreadMessagesCount = collect($conversationsCount)
                    ->where('conversation', $entry->conversation)
                    ->count();

                return self::getGroupConversationGeneralAttributes($user, $entry->event, $entry, $unreadMessagesCount);
            }
        })->filter(function ($conversation) {
            return $conversation ? true : false;
        })->values()
          ->toArray();
    }

    public function getMessages(): Collection
    {
        $messages = (new MessageRepository())->getMessages(
            $this->getChannel(),
            $this->getCurrentUser(),
            $this->getInterlocutor(),
            $this->getEvent(),
            $this->getLimit(),
            $this->getMaxTimestamp(),
            $this->getPage()
        );

        return $messages;
    }

    public function getUnreadMessages(): Collection
    {
        $messages = (new MessageRepository())->getUnreadMessages(
            $this->getChannel(),
            $this->getCurrentUser(),
            $this->getInterlocutor(),
            $this->getEvent()
        );

        return $messages;
    }

    public function getUnreadMessagesCount(): int
    {
        $count = (new MessageRepository())->getUnreadMessagesCount(
            $this->getChannel(),
            $this->getCurrentUser(),
            $this->getInterlocutor(),
            $this->getEvent()
        );

        return $count;
    }

    public function markMessagesAsRead(bool $cloak = false): void
    {
        $recipient = $this->getCurrentUser();
        $sender = $this->getInterlocutor();
        $event = $this->getEvent();
        $channel = $this->getChannel();
        $messageRepository = new MessageRepository();

        if ($channel == Message::CHANNEL_GROUP) {
            $latestRead = EventMessagesRead::firstOrNew([
                'event_id' => $event->id,
                'user_id'  => $recipient->id,
            ]);

            $latestRead->latest_read = \DB::raw('NOW');
            $latestRead->save();
            $this->markCacheMessageAsRead($sender, $recipient, $channel, $event);
            return;
        }

        $messagesToUpdate = $messageRepository->markMessagesAsRead($channel, $recipient, $sender, $event, $cloak);
        $this->markCacheMessageAsRead($sender, $recipient, $channel, $event);
        if ($cloak) {
            return;
        }

        foreach ($messagesToUpdate as $message) {
            /** @var Message $message */
            $messageData = $message->getGeneralAttributes();
            $messageData['is_read'] = 'yes';
            event(new UpdateMessageReceived($messageData));
        }
    }

    public function prepareMessagesCollectionToResponse(Collection $messages): array
    {
        $interlocutor = $this->getInterlocutor();
        $mediaDeleted =
            !empty($interlocutor)
            &&
            $interlocutor->isMediaDeleted();

        $messagesArray = $messages->map(function ($message) use ($mediaDeleted) {
            /** @var Message $message */
            $messageAsArray = $message->getGeneralAttributes($message->userFrom);

            if ($mediaDeleted) {
                $messageAsArray['image_id'] = null;
                $messageAsArray['video_id'] = null;
            }

            return $messageAsArray;
        })
        ->sortBy('idate')
        ->values()
        ->toArray();

        return $messagesArray;
    }

    /**
     * @param int $userId
     * @param array $messages
     * @return mixed
     */
    public function createConversationsCache(int $userId, array $messages)
    {
        $conversations = collect(json_decode(Redis::get('conversations:' . $userId), true));

        if (!empty($conversations)) {
            foreach ($messages as $message) {
                $conversations->push($message);
            }
        }

        $conversations = $conversations->unique(function ($item){
            $unique = $item['chatType'];

            if (isset($item['interlocutor'])) {
                $unique .= '-'.$item['interlocutor']['id'];
            }

            if (isset($item['event'])) {
                $unique .= '-'.$item['event']['id'];
            }

            return $unique;
        })->filter(function($item) use($userId) {
            $languages = ['de', 'en', 'es', 'fr', 'it', 'nl', 'pt'];

            foreach ($languages as $language) {
                if ($userId == env('BB_USER_ID', 100001) && $item['message']['message'] == trans('message.hello', [], $language)) {
                    return false;
                }
            }

            return true;
        })->values()->toArray();

        return Redis::set('conversations:' . $userId, json_encode($conversations));
    }

    /**
     * @param int $userId
     * @param array $messages
     * @return mixed
     */
    public function updateConversationsCache(int $userId, array $messages)
    {
        $messages = collect($messages)->unique(function ($item){
            $unique = $item['chatType'];

            if (isset($item['interlocutor'])) {
                $unique .= '-'.$item['interlocutor']['id'];
            }

            if (isset($item['event'])) {
                $unique .= '-'.$item['event']['id'];
            }

            return $unique;
        })->filter(function($item) use($userId) {
            $languages = ['de', 'en', 'es', 'fr', 'it', 'nl', 'pt'];

            foreach ($languages as $language) {
                if ($userId == env('BB_USER_ID', 100001) && $item['message']['message'] == trans('message.hello', [], $language)) {
                    return false;
                }
            }

            return true;
        })->values()->toArray();

        return Redis::set('conversations:' . $userId, json_encode($messages));
    }

    /**
     * @param User $user
     * @param User|null $recipient
     * @param $chatType
     * @param array $message
     * @param null $event
     */
    public function updateConversationsMessages(User $user, User $recipient = null, $chatType, array $message, $event = null)
    {
        $userId = $user->id;
        $recipientId = $recipient->id ?? null;

        if ($chatType === Message::CHANNEL_USER || $chatType === Message::CHANNEL_EVENT) {
            $this->updateCacheMessageForCurrentUser($userId, $recipientId, $message, $chatType);
            $this->updateCacheMessageForRecipient($user, $recipient, $message, $event, $chatType);
        } else if ($chatType === Message::CHANNEL_GROUP) {
            $this->updateCacheMessageForCurrentUser($userId, $recipientId, $message, $chatType);
            $this->updateCacheMessageForRecipients($user, $message, $event, $chatType);
        }
    }

    /**
     * @param int $userId
     * @param int|null $recipientId
     * @param array $message
     * @param string $chatType
     */
    public function updateCacheMessageForCurrentUser(int $userId, int $recipientId = null, array $message, string $chatType)
    {
        $conversationsUserTypes = json_decode(Redis::get('conversations:' . $userId), true);

        if (!empty($conversationsUserTypes)) {
            $conversationsUserTypes = array_values($conversationsUserTypes);
            $currentConversationKey = $this->getConversationKey($conversationsUserTypes, $userId, $recipientId, $message['event']['id'] ?? null, $chatType);

            if (!is_null($currentConversationKey) && is_int($currentConversationKey)) {
                unset($conversationsUserTypes[$currentConversationKey]);
            }

            $message['platform'] = Helper::isApp() ? 'mobile' : 'web';

            array_unshift($conversationsUserTypes, $message);

            Redis::del('conversations:' . $userId);

            $conversationsUserTypes = $this->duplicateCheck($conversationsUserTypes, $chatType);

            $this->updateConversationsCache($userId, $conversationsUserTypes);
        } else {
            $this->updateConversationsCache($userId, [$message]);
        }
    }

    /**
     * @param User $user
     * @param User $recipient
     * @param array $message
     * @param Event|null $event
     * @param string $chatType
     */
    public function updateCacheMessageForRecipient(User $user, User $recipient, array $message, ?Event $event = null, string $chatType)
    {
        $recipientId = $recipient->id;
        $userId = $user->id;
        $currentConversationKey = null;

        $conversationsRecipientTypes = json_decode(Redis::get('conversations:' . $recipientId), true);

        if (!empty($conversationsRecipientTypes)) {
            $conversationsRecipientTypes = array_values($conversationsRecipientTypes);
            $currentConversationKey = $this->getConversationKey($conversationsRecipientTypes, $userId, $recipientId, $message['event']['id'] ?? null, $chatType);
        }

        $message['interlocutor'] = $user->getAttributesByMode(User::ATTRIBUTES_MODE_CONVERSATION, $recipient);
        $message['interlocutor']['photo_small'] = $user->getPhotoUrl('180x180', false, $recipient);

        if ($user->isGhosted()) {
            $message['is_message_ghosted'] = true;
        }

        if (!is_null($currentConversationKey) && is_int($currentConversationKey)) {
            $unreadMessagesCount = (int) $conversationsRecipientTypes[$currentConversationKey]['unreadMessagesCount'];
            $message['unreadMessagesCount'] = $unreadMessagesCount + 1;
        } else {
            $message['unreadMessagesCount'] += 1;
        }

        if ($chatType === Message::CHANNEL_EVENT) {
            $message['event'] = $event->getAttributesByMode(Event::ATTRIBUTES_MODE_GENERAL, $recipient);
        }

        $message['platform'] = Helper::isApp() ? 'mobile' : 'web';

        if (!is_null($currentConversationKey)) {
            unset($conversationsRecipientTypes[$currentConversationKey]);
        }

        Redis::del('conversations:' . $recipientId);

        if (empty($conversationsRecipientTypes)) {
            $this->updateConversationsCache($recipientId, [$message]);
        } else {
            array_unshift($conversationsRecipientTypes, $message);

            $conversationsRecipientTypes = $this->duplicateCheck($conversationsRecipientTypes, $chatType);

            $this->updateConversationsCache($recipientId, $conversationsRecipientTypes);
        }
    }

    /**
     * @param User $user
     * @param $message
     * @param Event $event
     * @param string $chatType
     */
    public function updateCacheMessageForRecipients(User $user, $message, Event $event, string $chatType)
    {
        $userId = $user->id;
        $eventId = $event->id;
        $recipientsIds = [];

        if (!is_null($event)) {
            foreach ($message['event']['members'] as $member) {
                if ($member['id'] !== $userId) {
                    $recipientsIds[] = $member['id'];
                }
            }
        }

        $recipients = User::whereIn('id', $recipientsIds)->get();

        foreach ($recipients as $recipient) {
            $recipientId = $recipient->id;

            $recipientChatCache = json_decode(Redis::get('conversations:' . $recipientId), true);

            if (!empty($recipientChatCache) && !$user->isGhosted()) {
                $recipientChatCache = array_values($recipientChatCache);
                $recipientChatKey = null;

                foreach ($recipientChatCache as $key => $value) {
                    if (isset($value['event']) && $value['event']['id'] === $eventId && $value['chatType'] === $chatType) {
                        $recipientChatKey = $key;
                    }
                }

                if (!is_null($recipientChatKey) && is_int($recipientChatKey)) {
                    $unreadMessagesCount = (int) $recipientChatCache[$recipientChatKey]['unreadMessagesCount'];
                    $message['unreadMessagesCount'] = $unreadMessagesCount + 1;
                } else {
                    $message['unreadMessagesCount'] += 1;
                }

                $isHost = $event->user_id == ($recipientId ?? null);

                $message['event']['photo_small'] = $event->getPhotoUrl('180x180', $isHost, null, $recipient);
                $message['event']['photo_orig'] = $event->getPhotoUrl('orig', $isHost, null, $recipient);
                $message['event']['photo_rating'] = $event->getPhotoRating();
                $message['event']['isOnline'] = $event->user->isOnline();
                $message['event']['wasRecentlyOnline'] = $event->user->wasRecentlyOnline();

                if (!is_null($recipientChatKey) && is_int($recipientChatKey)) {
                    unset($recipientChatCache[$recipientChatKey]);
                }

                $message['platform'] = Helper::isApp() ? 'mobile' : 'web';

                array_unshift($recipientChatCache, $message);

                Redis::del('conversations:' . $recipientId);

                $recipientChatCache = $this->duplicateCheck($recipientChatCache, $chatType);

                $this->updateConversationsCache($recipientId, $recipientChatCache);
            }
        }
    }

    /**
     * @param User|null $sender
     * @param User $recipient
     * @param $channel
     * @param Event|null $event
     * @return bool
     */
    public function markCacheMessageAsRead(?User $sender = null, User $recipient, $channel, ?Event $event = null): bool
    {
        $selectConditions = [];
        if ($channel !== Message::CHANNEL_GROUP) {
            $selectConditions = [
                'user_from' => $sender->id,
                'user_to' => $recipient->id,
                'is_read' => 'no',
                'channel' => $channel
            ];
        }

        $allCachedConversations = json_decode(Redis::get('conversations:' . $recipient->id), true);
        if (!is_null($allCachedConversations)) {
            if ($channel === Message::CHANNEL_EVENT || $channel === Message::CHANNEL_USER) {
                foreach ($allCachedConversations as $key => $message) {
                    if (
                        $message['message']['user_from'] == $selectConditions['user_from']
                        &&
                        $message['message']['user_to'] == $selectConditions['user_to']
                        &&
                        $message['message']['is_read'] == $selectConditions['is_read']
                        &&
                        $message['message']['channel'] == $selectConditions['channel']
                    ) {
                        $allCachedConversations[$key]['message']['is_read'] = 'yes';
                        $allCachedConversations[$key]['unreadMessagesCount'] = 0;
                        Redis::del('conversations:' . $recipient->id);
                        $this->updateConversationsCache($recipient->id, $allCachedConversations);
                    }
                }
            } else if ($channel === Message::CHANNEL_GROUP) {

                foreach ($allCachedConversations as $key => $message) {
                    if (
                        isset($message['event']['id'])
                        &&
                        $message['event']['id'] === $event->id
                        &&
                        $message['message']['user_from'] !== $recipient->id
                    ) {
                        if ($allCachedConversations[$key]['unreadMessagesCount'] > 0) {
                            $allCachedConversations[$key]['unreadMessagesCount'] = 0;
                            Redis::del('conversations:' . $recipient->id);
                            $this->updateConversationsCache($recipient->id, $allCachedConversations);
                        }
                    }
                }
            }
        }


        return true;
    }

    /**
     * @param User $currentUser
     * @param string $type
     * @return bool
     */
    public function setGhostedOrActiveMessagesForRecipients(User $currentUser, string $type = 'active'): bool
    {
        $messageRepository = new MessageRepository();
        $recipientsIds = [];
        $groupChatsIds = [];
        $eventMembers = [];

        $allCachedConversations = json_decode(Redis::get('conversations:' . $currentUser->id), true);

        if (!is_null($allCachedConversations)) {
            foreach ($allCachedConversations as $conversation) {
                if ($conversation['chatType'] === Message::CHANNEL_USER || $conversation['chatType'] === Message::CHANNEL_EVENT) {
                    $recipientsIds[] = $conversation['interlocutor']['id'];
                } else if ($conversation['chatType'] === Message::CHANNEL_GROUP) {
                    $groupChatsIds[] = $conversation['event']['id'];
                }
            }

            $eventMembersMap = EventMembership::whereIn('event_id', $groupChatsIds)
                ->where('user_id', '!=', $currentUser->id)
                ->get();


            foreach ($eventMembersMap as $eventMember) {
                $eventMembers[$eventMember->event_id][] = $eventMember->user_id;
            }

            foreach ($recipientsIds as $recipientId) {
                $conversations = json_decode(Redis::get('conversations:' . $recipientId), true);

                if (!is_null($conversations)) {
                    $conversationKeys = $this->getConversationKey($conversations, $currentUser->id, $recipientId);

                    if (!is_null($conversationKeys)) {

                        if (is_array($conversationKeys)) {
                            foreach ($conversationKeys as $conversationKey) {
                                $conversations[$conversationKey]['is_message_ghosted'] = $type === 'active' ? 0 : 1;
                            }
                        } else if (is_int($conversationKeys)) {
                            $conversations[$conversationKeys]['is_message_ghosted'] = $type === 'active' ? 0 : 1;
                        }
                    }

                    Redis::del('conversations:' . $recipientId);
                    $this->updateConversationsCache($recipientId, $conversations);
                }
            }

            foreach ($eventMembers as $eventId => $eventMembersIds) {
                $event = Event::find($eventId);

                foreach ($eventMembersIds as $eventMemberId) {
                    $conversations = json_decode(Redis::get('conversations:' . $eventMemberId), true);

                    if (!is_null($conversations)) {
                        $conversationKeys = $this->getConversationKey($conversations, $currentUser->id, null, $eventId, Message::CHANNEL_GROUP);

                        if (is_int($conversationKeys)) {
                            $lastGroupMessage = $messageRepository->getLastGroupMessage($eventId, $type);

                            if (!is_null($lastGroupMessage)) {
                                $conversations[$conversationKeys] = self::getGroupConversationGeneralAttributes($currentUser, $event, $lastGroupMessage, 0);
                            }
                        }

                        Redis::del('conversations:' . $eventMemberId);
                        $this->updateConversationsCache($eventMemberId, $conversations);
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param $currentUserId
     * @param $interlocutorId
     * @param string $type
     * @return array
     */
    public function setMessageIsBlockedOrUnblocked($currentUserId, $interlocutorId, $type = 'unblock'): array
    {
        $usersConversations = [
            'currentUserConversations' => json_decode(Redis::get('conversations:' . $currentUserId), true),
            'interlocutorConversations' => json_decode(Redis::get('conversations:' . $interlocutorId), true)
        ];

        $usersUnblockConversations = [];

        foreach ($usersConversations as $key => $userConversations) {
            if (!is_null($userConversations)) {
                $userConversationKeys = null;

                if ($key === 'currentUserConversations') {
                    $userConversationKeys = $this->getConversationKey($userConversations, $currentUserId, $interlocutorId);
                } else if ($key === 'interlocutorConversations') {
                    $userConversationKeys = $this->getConversationKey($userConversations, $interlocutorId, $currentUserId);
                }

                if (is_int($userConversationKeys)) {
                    $userConversations[$userConversationKeys]['conversation_is_blocked'] = $type === 'unblock' ? 0 : 1;

                    if ($type === 'unblock') {
                        $usersUnblockConversations[$key][] = $userConversations[$userConversationKeys];
                    }

                } else if (is_array($userConversationKeys)) {
                    foreach ($userConversationKeys as $userConversationKey) {
                        $userConversations[$userConversationKey]['conversation_is_blocked'] = $type === 'unblock' ? 0 : 1;

                        if ($type === 'unblock') {
                            $usersUnblockConversations[$key][] = $userConversations[$userConversationKey];
                        }
                    }
                }

                if ($key === 'currentUserConversations') {
                    Redis::del('conversations:' . $currentUserId);
                    $this->updateConversationsCache($currentUserId, $userConversations);
                } else if ($key === 'interlocutorConversations') {
                    Redis::del('conversations:' . $interlocutorId);
                    $this->updateConversationsCache($interlocutorId, $userConversations);
                }
            }
        }

        return $usersUnblockConversations;
    }

    /**
     * @param User $currentUser
     * @param string $type
     */
    public function setMessageIsSuspendedOrActiveForRecipients(User $currentUser, $type = 'active')
    {
        $messageRepository = new MessageRepository();
        $currentUserId = $currentUser->id;
        $currentUserChatCache = json_decode(Redis::get('conversations:' . $currentUserId), true);
        $recipientsIds = [];
        $groupChatsIds = [];
        $eventMembers = [];

        if (!is_null($currentUserChatCache)) {
            foreach ($currentUserChatCache as $conversation) {
                if ($conversation['chatType'] === Message::CHANNEL_USER || $conversation['chatType'] === Message::CHANNEL_EVENT) {
                    $recipientsIds[] = $conversation['interlocutor']['id'];
                } else if ($conversation['chatType'] === Message::CHANNEL_GROUP) {
                    $groupChatsIds[] = $conversation['event']['id'];
                }
            }

            $eventMembersMap = EventMembership::whereIn('event_id', $groupChatsIds)
                ->where('user_id', '!=', $currentUserId)
                ->get();

            foreach ($eventMembersMap as $eventMember) {
                $eventMembers[$eventMember->event_id][] = $eventMember->user_id;
            }

            foreach ($recipientsIds as $recipientId) {
                $conversations = json_decode(Redis::get('conversations:' . $recipientId), true);

                if (!is_null($conversations)) {
                    $conversationKeys = $this->getConversationKey($conversations, $currentUserId, $recipientId);

                    if (!is_null($conversationKeys)) {

                        if (is_array($conversationKeys)) {
                            foreach ($conversationKeys as $conversationKey) {
                                $conversations[$conversationKey]['is_message_suspended'] = $type === 'active' ? 0 : 1;
                            }
                        } else if (is_int($conversationKeys)) {
                            $conversations[$conversationKeys]['is_message_suspended'] = $type === 'active' ? 0 : 1;
                        }
                    }

                    Redis::del('conversations:' . $recipientId);
                    $this->updateConversationsCache($recipientId, $conversations);
                }
            }

            foreach ($eventMembers as $eventId => $eventMembersIds) {
                $event = Event::find($eventId);

                foreach ($eventMembersIds as $eventMemberId) {
                    $conversations = json_decode(Redis::get('conversations:' . $eventMemberId), true);

                    if (!is_null($conversations)) {
                        $conversationKeys = $this->getConversationKey($conversations, $currentUserId, null, $eventId, Message::CHANNEL_GROUP);

                        if (is_int($conversationKeys)) {
                            $lastGroupMessage = $messageRepository->getLastGroupMessage($eventId, $type);

                            if (!is_null($lastGroupMessage)) {
                                $conversations[$conversationKeys] = self::getGroupConversationGeneralAttributes($currentUser, $event, $lastGroupMessage, 0);
                            }
                        }

                        Redis::del('conversations:' . $eventMemberId);
                        $this->updateConversationsCache($eventMemberId, $conversations);
                    }
                }
            }
        }
    }

    /**
     * @param $messageCache
     * @param int $userId
     * @param int|null $recipientId
     * @param int|null $eventId
     * @param string|null $chatType
     * @return array|null|int
     */
    protected function getConversationKey($messageCache, int $userId, int $recipientId = null, int $eventId = null, string $chatType = null)
    {
        $currentConversationKey = [];

        if (empty($messageCache) || !is_array($messageCache)) {
            $messageCache = [];
        }

        $messageCache = collect($messageCache);
        $currentConversationKey = $messageCache->filter(function ($conversation) use ($userId, $recipientId, $chatType, $eventId) {
            $fromMeToRecipient = $conversation['message']['user_to'] === $recipientId && $conversation['message']['user_from'] === $userId;
            $fromRecipientToMe = $conversation['message']['user_from'] === $recipientId && $conversation['message']['user_to'] === $userId;
            $fromMe = $conversation['message']['user_from'] === $userId;
            $fromOther = $conversation['message']['user_from'] !== $userId;

            $condition = (
                (
                    is_null($chatType)
                    ||
                    $conversation['chatType'] === $chatType
                )
                &&
                (
                    (is_null($chatType) || $chatType === Message::CHANNEL_USER || $chatType === Message::CHANNEL_EVENT && isset($conversation['event']) && $conversation['event']['id'] === $eventId)
                    &&
                    ($fromMeToRecipient || $fromRecipientToMe)
                    ||
                    ($chatType === Message::CHANNEL_GROUP && isset($conversation['event']) && $conversation['event']['id'] === $eventId)
                    &&
                    ($fromMe || $fromOther)
                )
            );

            return $condition;
        });
        $currentConversationKey = array_keys($currentConversationKey->toArray());

        return count($currentConversationKey) > 1 ? $currentConversationKey : $currentConversationKey[0] ?? null;
    }

    /**
     * @param array $conversations
     * @return array
     */
    protected function duplicateCheck(array $conversations): array
    {
        return collect($conversations)->unique(function ($item) {
            $unique = $item['chatType'];

            if (isset($item['interlocutor'])) {
                $unique .= '-'.$item['interlocutor']['id'];
            }

            if (isset($item['event'])) {
                $unique .= '-'.$item['event']['id'];
            }

            return $unique;
        })->values()->toArray();
    }

    /**
     * @param int $currentUserId
     * @param int $userId
     * @param bool $isFavorite
     */
    public function favoritesControl(int $currentUserId, int $userId, bool $isFavorite)
    {
        $conversations = json_decode(Redis::get('conversations:' . $currentUserId), true);

        if (empty($conversations) || !is_array($conversations)) {
            $conversations = [];
        }

        foreach ($conversations as $key => $conversation) {
            if ($conversation['chatType'] === Message::CHANNEL_USER && $conversation['interlocutor']['id'] === $userId) {
                $conversations[$key]['interlocutor']['isFavorite'] = $isFavorite;
            }
        }

        Redis::del('conversations:' . $currentUserId);
        $this->updateConversationsCache($currentUserId, $conversations);
    }

    /**
     * @param string $channel
     * @param int $currentUserId
     * @param int|null $interlocutorId
     * @param int|null $eventId
     */
    public function removeCacheConversation(string $channel, int $currentUserId, int $interlocutorId = null, ?int $eventId = null): void
    {
        $conversations = json_decode(Redis::get('conversations:' . $currentUserId), true);

        $conversationKey = $this->getConversationKey($conversations, $currentUserId, $interlocutorId, $eventId, $channel);

        if (!is_null($conversationKey)) {
            unset($conversations[$conversationKey]);
        }

        Redis::del('conversations:' . $currentUserId);
        $this->updateConversationsCache($currentUserId, array_values($conversations));
    }

    /**
     * @param string $channel
     * @param int $currentUserId
     * @param array|null $recipientsIds
     * @param int $eventId
     */
    public function removeCacheConversationForEventOrGroupRecipients(string $channel, int $currentUserId, ?array $recipientsIds = null, int $eventId): void
    {
        foreach ($recipientsIds as $recipientId) {
            $recipientChatCache = json_decode(Redis::get('conversations:' . $recipientId), true);

            if (!is_null($recipientChatCache)) {

                $recipientChatCache = array_values($recipientChatCache);

                if ($currentUserId === $recipientId) {
                    foreach ($recipientChatCache as $key => $conversation) {
                        if (isset($conversation['event']) && $conversation['event']['id'] === $eventId) {
                            unset($recipientChatCache[$key]);
                        }
                    }

                    Redis::del('conversations:' . $recipientId);
                    $this->updateConversationsCache($recipientId, array_values($recipientChatCache));
                } else {
                    $recipientChatCacheKey = $this->getConversationKey($recipientChatCache, $currentUserId, $recipientId, $eventId, $channel);

                    if (!is_null($recipientChatCacheKey)) {
                        unset($recipientChatCache[$recipientChatCacheKey]);
                        Redis::del('conversations:' . $recipientId);
                        $this->updateConversationsCache($recipientId, array_values($recipientChatCache));
                    }
                }
            }
        }
    }

    public static function getConversationGeneralAttributes(User $interlocutor, User $retriever, Message $message, int $unreadMessagesCount): array
    {
        return [
            'chatType' => 'user',
            'interlocutor' => $interlocutor->getAttributesByMode(User::ATTRIBUTES_MODE_CONVERSATION, $retriever),
            'message' => $message->getGeneralAttributes($retriever),
            'unreadMessagesCount' => $unreadMessagesCount
        ];
    }

    public static function getEventConversationGeneralAttributes(User $interlocutor, User $retriever, Event $event, Message $message, int $unreadMessagesCount): array
    {
        // Hide interlocutor's personal data
        $privacyEnabled = $retriever->id !== $event->user_id;
        return [
            'chatType' => 'event',
            'interlocutor' => $interlocutor->getAttributesByMode(User::ATTRIBUTES_MODE_CONVERSATION, $retriever, $privacyEnabled),
            'event' => $event->getAttributesByModeOld(Event::ATTRIBUTES_MODE_GENERAL, $retriever), // TODO: change to new method
            'message' => $message->getGeneralAttributes($retriever),
            'unreadMessagesCount' => $unreadMessagesCount
        ];
    }

    public static function getGroupConversationGeneralAttributes(?User $retriever, Event $event, Message $message, int $unreadMessagesCount): array
    {
        return [
            'chatType' => 'group',
            'event' => $event->getAttributesByMode(Event::ATTRIBUTES_MODE_GENERAL, $retriever),
            'message' => $message->getGeneralAttributes($retriever),
            'unreadMessagesCount' => $unreadMessagesCount
        ];
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int|null $limit
     */
    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param int|null $page
     */
    public function setPage(?int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return string|null
     */
    public function getMaxTimestamp(): ?string
    {
        return $this->maxTimestamp;
    }

    /**
     * @param string|null $maxTimestamp
     */
    public function setMaxTimestamp(?string $maxTimestamp): void
    {
        $this->maxTimestamp = $maxTimestamp;
    }

    /**
     * @return User|null
     */
    public function getInterlocutor(): ?User
    {
        return $this->interlocutor;
    }

    /**
     * @param User|null $interlocutor
     */
    public function setInterlocutor(?User $interlocutor): void
    {
        $this->interlocutor = $interlocutor;
    }

    /**
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        return $this->currentUser;
    }

    /**
     * @param User|null $currentUser
     */
    public function setCurrentUser(?User $currentUser): void
    {
        $this->currentUser = $currentUser;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     */
    public function setChannel(string $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return Event|null
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * @param Event|null $event
     */
    public function setEvent(?Event $event): void
    {
        $this->event = $event;
    }
}
