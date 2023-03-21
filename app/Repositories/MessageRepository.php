<?php namespace App\Repositories;

use App\User;
use App\Event;
use App\Message;
use App\UserPhoto;
use App\UserVideo;
use Carbon\Carbon;
use App\UserBlocked;
use App\EventMembership;

use App\Events\CheckMessage;
use App\Services\ChatService;
use MongoDB\BSON\UTCDateTime;
use App\Jobs\ProcessMassMessages;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Jobs\CreateConversationsCache;
use App\Models\Event\EventMessagesRead;
use phpDocumentor\Reflection\Types\Mixed_;
use App\Models\Message\InternalMessagesQueue;

class MessageRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $usersGhostedIds;

    /**
     * @var array
     */
    protected $userBlockStatuses = [
        User::STATUS_GHOSTED,
        User::STATUS_SUSPENDED
    ];

    public function __construct(Message $model = null)
    {
        if (empty($model)) {
            $model = new Message();
        }
        parent::__construct($model);
    }

    /**
     * @param array $data
     *
     * @return Message
     * @throws \Exception
     */
    public function createMessage(array $data): Message
    {
        if ($data['msg_type'] == Message::TYPE_TEXT && (empty($data['message']) && $data['message'] !== '0')) {
            throw new \Exception('Message content was not set');
        }

        if ($data['msg_type'] == Message::TYPE_VIDEO && empty($data['video_id'])) {
            throw new \Exception('Message video_id was not set');
        }

        if ($data['msg_type'] == Message::TYPE_IMAGE && empty($data['image_id'])) {
            throw new \Exception('Message image_id was not set');
        }

        $data['conversation'] = self::getConversationHash(
            $data['channel'],
            $data['user_from'],
            $data['user_to'] ?? null,
            $data['event_id'] ?? null
        );

        if (
            !empty($data['message'])
            &&
            empty($data['is_bulk'])
        ) {
            $data['ml'] = strlen($data['message']);
        }

        $data['is_read'] = 'no';
        $data['is_read_cloak'] = 0;
        $data['is_bulk'] = $data['is_bulk'] ?? 0;
        $data['idate'] = new \DateTime();

        return $this->model->create($data);
    }

    public function getConversationAndClearCache($interlocutor,
                                            $currentUser,
                                            $message,
                                            $channel,
                                            $event=null,
                                            $eventId=null)
    {
        $conversationResponse = null;

        if ($channel == Message::CHANNEL_USER) {
            $conversationResponse = ChatService::getConversationGeneralAttributes($interlocutor, $currentUser, $message, 0);
        } elseif ($channel == Message::CHANNEL_EVENT) {
            $conversationResponse = ChatService::getEventConversationGeneralAttributes($interlocutor, $currentUser, $event, $message, 0);
        } elseif ($channel == Message::CHANNEL_GROUP) {
            $conversationResponse = ChatService::getGroupConversationGeneralAttributes($currentUser, $event, $message, 0);
        }

        event(new CheckMessage($conversationResponse, $currentUser->id, $eventId));

        (new ChatService())->updateConversationsMessages($currentUser, $interlocutor, $channel, $conversationResponse, $event);

        return $conversationResponse;
    }

    /**
     * @param string $channel
     * @param int $senderId
     * @param int|null $recipientId
     * @param int|null $eventId
     *
     * @return string
     * @throws \Exception
     */
    protected static function getConversationHash(string $channel, int $senderId, ?int $recipientId, ?int $eventId): string
    {
        $hash = null;
        switch ($channel) {
            case Message::CHANNEL_USER:
            {
                $sender = max($senderId, $recipientId);
                $recipient = min($senderId, $recipientId);
                $hash = md5("$channel:$sender:$recipient");
                break;
            }
            case Message::CHANNEL_EVENT:
            {
                $sender = max($senderId, $recipientId);
                $recipient = min($senderId, $recipientId);
                $hash = md5("$channel:$sender:$recipient:$eventId");
                break;
            }
            case Message::CHANNEL_GROUP:
            {
                $hash = md5("$channel:$eventId");
                break;
            }
            default:
            {
                throw new \Exception('Unknown message channel', 500);
            }
        }
        return $hash;
    }

    public function getUnreadMessagesStat(User $currentUser, Collection $users)
    {
        $userIds = $users
            ->map(function (User $user) {
                return $user->id;
            })
            ->toArray();

        $result = Message::raw(function ($collection) use ($currentUser, $userIds) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'user_to' => $currentUser->id,
                        'user_from' => ['$in' => $userIds],
                        'is_read' => 'no',
                        'is_read_cloak' => 0,
                        'is_bulk' => 0,

                        'cancelled' => ['$in' => [0, null]],
                        'is_removed_by_sender' => ['$in' => [0, null]], // Legacy
                        'deleted' => ['$in' => [0, null]], // Legacy
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$user_from',
                        'unreadMessagesCount' => ['$sum' => 1]
                    ]
                ]
            ]);
        });

        $stat = collect($result)->mapWithKeys(function ($entry) {
            return [$entry->_id => $entry->unreadMessagesCount];
        })->toArray();

        return $stat;
    }

    /**
     * @param string $interval
     * @param string $currentDay
     * @return array
     */
    public function getUsersWithUnreadMessages(string $interval = 'weekly', string $currentDay = 'monday'): array
    {
        $days = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6
        ];

        $currentDateValue = $days[$currentDay];

        if ($interval == 'daily') {
            $minDate = Carbon::now()->subDay()->startOfDay();
            $periodDays = 1;
        } elseif ($interval == 'weekly') {
            $minDate = Carbon::now()->subDays(7)->startOfDay();
            $periodDays = 7;
        } elseif ($interval == 'monthly') {
            $minDate = Carbon::now()->subMonth()->startOfDay();
            $periodDays = 30;
        } else {
            \Log::error('Invalid reminder interval');
            dd('Invalid reminder interval');
        }

        $period = 60 * 60 * 24 * $periodDays;

            /** @var Collection $userStats */
        $userStats = Message::with('userTo')
            ->raw(function ($collection) use ($minDate, $interval) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'is_read' => 'no',
                            'is_read_cloak' => 0,
                            'is_sender_suspended' => ['$in' => [0, null]],
                            'idate' => ['$gte' => new UTCDateTime($minDate->format('Uv'))],

                            'cancelled' => ['$in' => [0, null]],
                            'is_removed_by_sender' => ['$in' => [0, null]], // Legacy
                            'deleted' => ['$in' => [0, null]], // Legacy
                        ],
                    ],
                    [
                        '$group' => [
                            '_id' => '$user_to',
                            'unreadMessagesCount' => ['$sum' => 1]
                        ]
                    ]
                ]);
            });

        // Post-processing:
        // users.email_reminders = $interval
        // users.status = active
        // users.deleted_at is NULL
        // users.email_validation != 'bounce' or users.email_validation is NULL

        $userStatChunks = $userStats->chunk(100);

        $entries = [];
        foreach ($userStatChunks as $userStatChunk) {
            /** @var Collection $userStatChunk */
            $userMap = $userStatChunk->mapWithKeys(function ($entry) {
                return [$entry->_id => $entry->unreadMessagesCount];
            })->toArray();

            $users = User::whereIn('id', $userStatChunk->pluck('_id'))->get();
            foreach ($users as $user) {
                /** @var User $user */
                if (
                    $user->email_reminders == $interval
                    &&
                    $user->status == User::STATUS_ACTIVE
                    &&
                    !$user->isDeleted()
                    &&
//                    false === $user->activeWithinPeriod($user->last_active, $period)
//                    &&
                    ($user->email_validation != 'bounce' || empty($user->email_validation))
                ) {
                    $entries[] = [
                        'user' => $user,
                        'unreadMessagesCount' => $userMap[$user->id] ?? 0,
                    ];
                }
            }
        }

        return $entries;
    }

    /**
     * @param string $channel
     * @param int|null $currentUserId
     * @param int|null $interlocutorId
     * @param int|null $eventId
     *
     * @return array
     */
    public function getImages(string $channel, ?int $currentUserId, ?int $interlocutorId, ?int $eventId): array
    {
        $query = $this
            ->with('image')
            ->where([
                'channel' => $channel,
                'msg_type' => 'image',
            ])
            ->where([
                ['cancelled', '!=', 1],
                ['is_removed_by_sender', '!=', 1],
                ['deleted', '!=', 1],
            ])
            ->orderBy('idate', 'desc');

        if (
            !empty($currentUserId)
            &&
            !empty($interlocutorId)
        ) {
            $query->where(function ($query) use ($currentUserId, $interlocutorId) {
                return $query
                    ->where([
                        ['user_from', '=', $currentUserId],
                        ['user_to', '=', $interlocutorId],
                        ['deleted_for_user_from', '!=', 1]
                    ])
                    ->orWhere([
                        ['user_from', '=', $interlocutorId],
                        ['user_to', '=', $currentUserId],
                        ['deleted_for_user_to', '!=', 1]
                    ]);
            });
        }

        if (!empty($eventId)) {
            $query->where('event_id', $eventId);
        }

        $messages = $query->get(['_id', 'user_from', 'image_id']);

        $images = [];
        /** @var Message $message */
        foreach ($messages as $message) {
            /** @var UserPhoto $image */
            $image = $message->image;
            if (empty($image)) {
                continue;
            }

            $image->setUrls(true);
            $images[] = [
                'id' => $message->_id,
                'photo_orig' => $image->getUrl('orig', true),
                'user_from' => $message->user_from
            ];
        }

        return $images;
    }

    /**
     * @param string $channel
     * @param int|null $currentUserId
     * @param int|null $interlocutorId
     * @param int|null $eventId
     *
     * @return array
     */
    public function getVideos(string $channel, ?int $currentUserId, ?int $interlocutorId, ?int $eventId): array
    {
        $query = $this
            ->with('video')
            ->where([
                'channel' => $channel,
                'msg_type' => 'video',
            ])
            ->where([
                ['cancelled', '!=', 1],
                ['is_removed_by_sender', '!=', 1],
                ['deleted', '!=', 1],
            ])
            ->orderBy('idate', 'desc');

        if (
            !empty($currentUserId)
            &&
            !empty($interlocutorId)
        ) {
            $query->where(function ($query) use ($currentUserId, $interlocutorId) {
                return $query
                    ->where([
                        ['user_from', '=', $currentUserId],
                        ['user_to', '=', $interlocutorId],
                        ['deleted_for_user_from', '!=', 1]
                    ])
                    ->orWhere([
                        ['user_from', '=', $interlocutorId],
                        ['user_to', '=', $currentUserId],
                        ['deleted_for_user_to', '!=', 1]
                    ]);
            });
        }

        if (!empty($eventId)) {
            $query->where('event_id', $eventId);
        }

        $messages = $query->get(['_id', 'video_id']);

        $videos = [];
        foreach ($messages as $message) {
            /** @var Message $message */
            if (!empty($message->video)) {
                $message->video->setUrls(true);
                $videos[] = [
                    'id' => $message->_id,
                    'thumb_orig' => $message->video['thumb_orig'],
                    'thumb_small' => $message->video['thumb_small'],
                    'video_url' => $message->video['video_url'],
                ];
            }
        }

        return $videos;
    }

    /**
     * @param string $channel
     * @param User|null $currentUser
     * @param User|null $interlocutor
     * @param Event|null $event
     * @param int $limit
     * @param string|null $maxTimestamp
     * @param int $page
     *
     * @return Collection
     * @throws \Exception
     */
    public function getMessages(
        string $channel,
        ?User $currentUser,
        ?User $interlocutor,
        ?Event $event,
        int $limit = 10,
        string $maxTimestamp = null,
        int $page = 0
    ): Collection
    {
        $query = $this->with([
            'image' => function ($query) {
                $query->select('id', 'photo', 'slot', 'nudity_rating', 'manual_rating', 'status');
            },
            'video' => function ($query) {
                $query->select('id', 'video_name', 'thumbnail_type', 'nudity_rating', 'manual_rating', 'status');
            }
        ])->where('channel', $channel);

        if (
            !empty($currentUser)
            &&
            !empty($interlocutor)
        ) {
            $query->where(function ($query) use ($currentUser, $interlocutor) {
                return $query
                    ->where([
                        ['user_from', '=', $currentUser->id],
                        ['user_to', '=', $interlocutor->id],
                        ['deleted_for_user_from', '!=', 1]
                    ])
                    ->orWhere([
                        ['user_from', '=', $interlocutor->id],
                        ['user_to', '=', $currentUser->id],
                        ['deleted_for_user_to', '!=', 1],
                        ['is_sender_ghosted', '!=', 1]
                    ]);
            });
        }


        if (!empty($event)) {
            $query->where('event_id', $event->id);
            /*$query->with(['userFrom' => function ($query) {
                $query->select(
                    'id', 'link', 'name', 'last_active', 'discreet_mode',
                    'pro_expires_at', 'show_age', 'dob', 'height', 'weight',
                    'position'
                );
            }])
            ->whereHas('userFrom', function ($query) use ($currentUser) {
                $query->select('id', 'status')
                      ->where('id', $currentUser->id)
                      ->orWhere('status', '!=', User::STATUS_GHOSTED);
            })*/;
        }

        if (!empty($maxTimestamp)) {
            $query->where('idate', '<', new \DateTime($maxTimestamp));
        }

        $page = $page ?? 0;

        $items = $query->forPage($page + 1, $limit)->orderBy('idate', 'desc')->get();

        $videoIds = [];
        $photoIds = [];
        $userFrom = [];
        foreach ($items as $item) {
            if ($item->image_id) {
                $videoIds[$item->video_id] = 1;
            }

            if ($item->image_id) {
                $photoIds[$item->image_id] = 1;
            }

            if ($item->user_from) {
                $userFrom[$item->user_from] = 1;
            }
        }

        $videoFiles = count($videoIds) ? UserVideo::whereIn('id', array_keys($videoIds))->get() : collect([]);
        $photoFiles = count($photoIds) ? UserPhoto::whereIn('id', array_keys($photoIds))->get() : collect([]);
        $senders    = count($userFrom) ? User::whereIn('id', array_keys($userFrom))->get()      : collect([]);

        foreach ($items as &$item) {
            $item->image    = $item->image_id  ? $photoFiles->where('id', '=', $item->image_id)->first() : null;
            $item->video    = $item->video_id  ? $videoFiles->where('id', '=', $item->video_id)->first() : null;
            $item->userFrom = $item->user_from ? $senders->where('id', '=', $item->user_from)->first()   : null;
        }

        return $items;
    }

    /**
     * @param string $channel
     * @param User $currentUser
     * @param User $interlocutor
     * @param Event|null $event
     *
     * @return Collection
     */
    public function getUnreadMessages(string $channel, User $currentUser, User $interlocutor, ?Event $event): Collection
    {
        $query = $this
            ->with(['image', 'video'])
            ->where([
                'user_to' => $currentUser->id,
                'user_from' => $interlocutor->id,
                'is_read' => 'no',
                'is_read_cloak' => 0,
                'channel' => $channel,
            ])
            ->where([
                ['deleted_for_user_to', '!=', 1],
                ['cancelled', '!=', 1],
                ['is_removed_by_sender', '!=', 1],
                ['deleted', '!=', 1],
            ])
            ->orderBy('idate', 'desc');

        if (!empty($event)) {
            $query->where('event_id', $event->id);
        }

        $messages = $query->get();

        return $messages;
    }

    /**
     * @param $conversationsIds
     * @param User $user
     * @param $eventsIds
     * @return array
     */
    public function getUnreadMessagesCountChats($conversationsIds, User $user, $eventsIds): array
    {
        $latestRead = null;

        if (!empty($eventsIds)) {
            $latestRead = EventMessagesRead::where('user_id', $user->id)
                ->whereIn('event_id', $eventsIds)
                ->get();
        }

        $allMessages = $this->where([
            'is_read' => 'no',
            'is_read_cloak' => 0
        ])->whereIn('conversation', $conversationsIds)
            ->where([
                ['user_from', '!=', $user->id],
                ['is_sender_ghosted', '!=', 1],
                ['deleted_for_user_to', '!=', 1],
                ['cancelled', '!=', 1],
                ['is_removed_by_sender', '!=', 1],
                ['deleted', '!=', 1],
            ])->get();

        $conversationMap = [];

        foreach ($allMessages as $message) {
            if ($message->channel === Message::CHANNEL_GROUP) {
                if ($message->user_from !== $user->id && $message->msg_type !== Message::TYPE_JOINED) {
                    $latestReadMessage = $latestRead->where('event_id', $message->event_id)->first();

                    if (is_object($latestReadMessage)) {
                        $latestReadDate = strtotime($latestReadMessage->latest_read);
                        $messageIdate = strtotime($message->idate);

                        if ($messageIdate > $latestReadDate) {
                            $conversationMap[] = $message;
                        }
                    }
                }
            } else {
                $conversationMap[] = $message;
            }
        }

        return $conversationMap;
    }

    /**
     * @param string $channel
     * @param User $currentUser
     * @param User $interlocutor
     * @param Event|null $event
     *
     * @return int
     */
    public function getUnreadMessagesCount(string $channel, User $currentUser, User $interlocutor, ?Event $event): int
    {
        $query = $this
            ->where([
                'user_to' => $currentUser->id,
                'user_from' => $interlocutor->id,
                'is_read' => 'no',
                'is_read_cloak' => 0,
                'channel' => $channel,
            ])
            ->where([
                ['deleted_for_user_to', '!=', 1],
                ['cancelled', '!=', 1],
                ['is_removed_by_sender', '!=', 1],
                ['deleted', '!=', 1],
            ]);

        if (!empty($event)) {
            $query->where('event_id', $event->id);
        }

        $count = $query->count();

        return $count;
    }

    /**
     * @param string $channel
     * @param User $recipient
     * @param User $sender
     * @param Event $event
     * @param bool $cloak
     *
     * @return Collection
     */
    public function markMessagesAsRead(
        string $channel,
        User $recipient,
        User $sender,
        ?Event $event,
        bool $cloak = false
    ): Collection
    {
        $selectConditions = [
            'user_from' => $sender->id,
            'user_to' => $recipient->id,
            'is_read' => 'no',
            'channel' => $channel
        ];
        if (!empty($event)) {
            $selectConditions['event_id'] = $event->id;
        }
        $messages = $this->where($selectConditions)->get();

        $updateConditions = ['is_read_cloak' => 1];
        if (!$cloak) {
            $updateConditions['is_read'] = 'yes';
        }
        $this
            ->where($selectConditions)
            ->updateAll($updateConditions);

        return $messages;
    }

    /**
     * @param string $messageId
     * @param int $senderId
     *
     * @return array
     */
    public function markMessageAsCancelled(string $messageId, int $senderId): array
    {
        $cancelled = false;
        $messageData = null;

        /** @var Message $message */
        $message = Message::where([
            '_id' => $messageId,
            'user_from' => $senderId
        ])->first();

        if (!empty($message)) {
            $message->cancelled = 1;
            $cancelled = $message->save();
            $messageData = $message->fresh()->getGeneralAttributes();
        }

        Redis::del('conversations:' . $senderId);
        CreateConversationsCache::dispatch($senderId)->delay(0);

        return [
            'cancelled' => $cancelled,
            'message' => $messageData
        ];
    }

    /**
     * @param string $channel
     * @param int $currentUserId
     * @param int $interlocutorId
     * @param int|null $eventId
     *
     * @return void
     * @throws \Exception
     */
    public function removeConversation(string $channel, int $currentUserId, int $interlocutorId, ?int $eventId = null): void
    {
        $query = $this->where('channel', $channel);
        if (
            !empty($currentUserId)
            &&
            !empty($interlocutorId)
        ) {
            $query->where([
                'user_from' => $currentUserId,
                'user_to' => $interlocutorId
            ]);
        } else {
            throw new \Exception('You cannot delete conversation when user ids are not defined', 500);
        }

        if (!empty($eventId)) {
            $query->where('event_id', $eventId);
        }

        $query->updateAll(['deleted_for_user_from' => 1]);


        $query = $this->where('channel', $channel);
        if (
            !empty($currentUserId)
            &&
            !empty($interlocutorId)
        ) {
            $query->where([
                'user_from' => $interlocutorId,
                'user_to' => $currentUserId
            ]);
        } else {
            throw new \Exception('You cannot delete conversation when user ids are not defined', 500);
        }

        if (!empty($eventId)) {
            $query->where('event_id', $eventId);
        }

        $query->updateAll(['deleted_for_user_to' => 1]);
    }

    /**
     * @param int $userId
     * @param Carbon $minDate
     *
     * @return int
     */
    public function getMessagedUsersCountFromUser(int $userId, Carbon $minDate): int
    {
        $count = Message::where([
            ['user_from', '=', $userId],
            ['idate', '>=', $minDate],
        ])
            ->distinct('user_to')
            ->get()
            ->count();

        return $count;
    }

    /**
     * @param $imageId
     *
     * @return boolean
     */
    public function detachImage(int $imageId): bool
    {
        $this->where('image_id', $imageId)
             ->updateAll([
                 'message'  => '',
                 'image_id' => null,
             ]);

        return true;
    }

    /**
     * @param $videoId
     *
     * @return boolean
     */
    public function detachVideo(int $videoId): bool
    {
        $this->where('video_id', $videoId)
             ->updateAll([
                 'message'  => '',
                 'video_id' => null,
             ]);

        return true;
    }

    /**
     * @param User $sender
     * @param array $targetUsers
     * @param string $message
     * @return mixed
     */
    public function massSendMessage(User $sender, array $targetUsers, string $message)
    {
        $targetUsersCount = count($targetUsers);
        if ($targetUsersCount) {
            $messageData = [
                'text' => $message,
                'sender_id' => $sender->id,
            ];
            $queue = InternalMessagesQueue::create([
                'messages' => $targetUsersCount,
            ]);

            $chunks = array_chunk($targetUsers, 5000);
            foreach ($chunks as $chunk) {
                ProcessMassMessages::dispatch($queue->id, $messageData, $chunk)->onQueue('ProcessMassMessages');
            }
            return true;
        }

        return false;
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getAllUserEventsIds(int $userId): array
    {
        $statuses = [
            EventMembership::STATUS_REMOVED,
            EventMembership::STATUS_LEAVED,
            EventMembership::ACTION_REMOVE
        ];

        /** Getting removed user event ids */
        $removedUserEventsIds = EventMembership::where('user_id', $userId)
                                                ->with('event')
                                                ->has('event')
                                                ->whereIn('status', $statuses)
                                                ->get()
                                                ->pluck('event_id')
                                                ->toArray();

        /** Getting event IDs where the user has ever written */
        $userAllEvents = Message::with('messageEvent')
                                ->has('messageEvent')
                                ->where('user_from', $userId)
                                ->whereNotIn('event_id', $removedUserEventsIds)
                                ->groupBy('event_id')
                                ->get()
                                ->pluck('event_id')
                                ->toArray();

        /** Receiving user events that he has created */
        $userEvents = Event::select('id')
                            ->where('user_id', $userId)
                            ->get()
                            ->pluck('id');

        return collect($userAllEvents)
                ->merge($userEvents)
                ->unique()
                ->values()
                ->toArray();
    }

    /**
     * @return array
     */
    public function getSpecialTexts(): array
    {
        $languages = ['de', 'en', 'es', 'fr', 'it', 'nl', 'pt'];

        foreach ($languages as $language) {
            $specialTexts[] = [
                'message' => ['$ne' => trans('message.hello', [], $language)],
            ];
        }

        return $specialTexts;
    }

    /**
     * @param int $userId
     * @param int $limit
     * @param int $offset
     *
     * @return Collection
     */
    public function getConversationMessagesAll(int $userId, int $limit, int $offset, User $user): Collection
    {
        $allEvents = $this->getAllUserEventsIds($userId);

        // If the current user is a ghost, we show all
        // the messages that came to him, the normal user we hide messages from the ghost user
        if (!$user->isGhosted()) {
            $query = [
                'is_recipient_ghosted' => ['$in' => [0, null]],
                'is_sender_ghosted' => ['$in' => [0, null]],
            ];
        } else {
            $query = [
                'is_recipient_ghosted' => ['$in' => [0,1]],
                'is_sender_ghosted' => ['$in' => [0,1]],
            ];
        }

        $conversations = Message::raw(function($collection) use ($userId, $limit, $offset, $allEvents, $query) {
            $queries = [
                [
                    '$match' => [
                        '$and' => [
                            // add query here,
                            [
                                '$or' => [
                                    [
                                        'user_from' => $userId,
                                        'deleted_for_user_from' => ['$in' => [0, null]],
                                        'is_recipient_ghosted' => $query['is_recipient_ghosted'],
                                        'is_sender_ghosted' => $query['is_sender_ghosted']
                                    ],
                                    [
                                        'user_to' => $userId,
                                        'is_sender_ghosted' => ['$in' => [0, null]],
                                        'deleted_for_user_to' => ['$in' => [0, null]]
                                    ],
                                    [
                                        'channel' => Message::CHANNEL_GROUP,
                                        'is_sender_ghosted' => $query['is_sender_ghosted']
                                    ]
                                ],
                            ],
                            [
                                '$or' => [
                                    ['channel' => Message::CHANNEL_EVENT],
                                    ['channel' => Message::CHANNEL_GROUP],
                                    ['channel' => Message::CHANNEL_USER]
                                ]
                            ],
                            [
                                'is_blocked_by_sender' => ['$in' => [0, null]],
                                'is_blocked_by_recipient' => ['$in' => [0, null]],
                            ],
                            [
                                '$or' => [
                                    [
                                        '$and' => [
                                            ['is_bulk' => 1],
                                            ['user_from' => ['$ne' => $userId]],
                                        ],
                                    ],
                                    ['is_bulk' => ['$in' => [0, null]]],
                                ],
                            ],
                            [
                                '$and' => [
                                    [
                                        '$or' => [
                                            [
                                                'event_id' => [
                                                    '$in' => $allEvents,
                                                    '$exists' => true
                                                ],
                                            ],
                                            [
                                                'event_id' => [
                                                    '$exists' => false
                                                ]
                                            ]
                                        ]
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                ['$sort' => ['idate' => -1]],
                [
                    '$group' => [
                        '_id' => '$conversation',
                        'conversation' => ['$first' => '$conversation'],
                        'user_from' => ['$first' => '$user_from'],
                        'user_to' => ['$first' => '$user_to'],
                        'event_id' => ['$first' => '$event_id'],
                        'message' => ['$first' => '$message'],
                        'msg_type' => ['$first' => '$msg_type'],
                        'image_id' => ['$first' => '$image_id'],
                        'video_id' => ['$first' => '$video_id'],
                        'channel' => ['$first' => '$channel'],
                        'is_read' => ['$first' => '$is_read'],
                        'is_read_cloak' => ['$first' => '$is_read_cloak'],
                        'is_sender_suspended' => ['$first' => '$is_sender_suspended'],
                        'is_bulk' => ['$first' => '$is_bulk'],
                        'idate' => ['$first' => '$idate'],
                        'cancelled' => ['$first' => '$cancelled'],
                    ]
                ],
                ['$sort' => ['idate' => -1]],
                ['$skip' => $offset],
                ['$limit' => $limit]
            ];

            if ($userId == env('BB_USER_ID', 100001)) {
                array_push($queries[0]['$match']['$and'], [
                    '$and' => $this->getSpecialTexts(),
                ]);
            }

            return $collection->aggregate(
                $queries
            );
        });

        return $conversations;
    }

    /**
     * @param int $userId
     * @param int $limit
     * @param int $offset
     *
     * @return Collection
     */
    public function getConversationMessagesUnread(int $userId, int $limit, int $offset): Collection
    {
        $allUserEvents = $this->getAllUserEventsIds($userId);

        $allMessages = Message::raw(function ($collection) use ($allUserEvents, $userId) {
            $queries = [
                [
                    '$match' => [
                        '$and' => [
                            // query
                            [
                                'event_id' => [
                                    '$in' => $allUserEvents,
                                    '$exists' => true
                                ],
                                'channel' => ['$in' => [Message::CHANNEL_EVENT, Message::CHANNEL_GROUP]],
                                'user_from' => ['$ne' => $userId],
                                'is_read' => 'no',
                                'is_read_cloak' => 0,
                                'deleted_for_user_to' => ['$in' => [0, null]],
                                'cancelled' => ['$in' => [0, null]],
                                'is_blocked_by_sender' => ['$in' => [0, null]],
                                'deleted' => ['$in' => [0, null]],
                                'is_sender_ghosted' => ['$in' => [0, null]]
                            ]
                        ]
                    ]
                ],
            ];

            if ($userId == env('BB_USER_ID', 100001)) {
                array_push($queries[0]['$match']['$and'], [
                    '$and' => $this->getSpecialTexts(),
                ]);
            }

            return $collection->aggregate(
                $queries
            );
        });


        $latestReadData = EventMessagesRead::where('user_id', $userId)
                                            ->whereIn('event_id', $allUserEvents)
                                            ->get();
        $unreadEventsIds = [];

        foreach ($allMessages as $message) {
            $lastReadMessage = null;

            if ($message->channel === Message::CHANNEL_GROUP) {
                $lastReadMessage = $latestReadData->where('event_id', $message->event_id)->first();
            }

            if (!is_null($lastReadMessage)) {
                $lastReadDate = strtotime($lastReadMessage->latest_read);
                $messageIdate = strtotime($message->idate);

                if ($messageIdate > $lastReadDate) {
                    $unreadEventsIds[] = $message->event_id;
                }
            } else if ($message->channel === Message::CHANNEL_EVENT) {
                $unreadEventsIds[] = $message->event_id;
            }
        }

        $result = Message::raw(function($collection) use ($userId, $limit, $offset, $unreadEventsIds) {
                return $collection->aggregate(
                    [
                        [
                            '$match' => [
                                '$and' => [
                                    [
                                        '$or' => [
                                            [
                                                'user_to' => $userId,
                                                'user_from' => ['$ne' => $userId],
                                                '$or' => [
                                                    [
                                                        'event_id' => [
                                                            '$in' => $unreadEventsIds,
                                                            '$exists' => true
                                                        ]
                                                    ],
                                                    [
                                                        'event_id' => ['$exists' => false]
                                                    ]
                                                ]
                                            ],
                                            [
                                                'channel' => Message::CHANNEL_GROUP,
                                                'event_id' => [
                                                    '$in' => $unreadEventsIds,
                                                    '$exists' => true
                                                ],
                                            ]

                                    ],
                                    'is_read' => 'no', # Was not read
                                    'is_read_cloak' => 0, # Was not read in discreet mode
                                    'is_sender_suspended' => ['$in' => [0, null]],
                                    'is_sender_ghosted' => ['$in' => [0, null]],
                                    'is_blocked_by_sender' => ['$in' => [0, null]],
                                    'is_blocked_by_recipient' => ['$in' => [0, null]],
                                    'deleted_for_user_to' => ['$in' => [0, null]],
                                    'deleted_for_user_from' => ['$in' => [0, null]]
                                ],
                                [
                                    '$or' => [
                                        [
                                            '$and' => [
                                                ['is_bulk' => 1],
                                                ['user_from' => ['$ne' => $userId]],
                                            ],
                                        ],
                                        ['is_bulk' => ['$in' => [0, null]]],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    ['$sort' => ['idate' => -1]],
                    [
                        '$group' => [
                            '_id' => '$conversation',
                            'conversation' => ['$first' => '$conversation'],
                            'user_from' => ['$first' => '$user_from'],
                            'user_to' => ['$first' => '$user_to'],
                            'event_id' => ['$first' => '$event_id'],
                            'message' => ['$first' => '$message', ],
                            'msg_type' => ['$first' => '$msg_type'],
                            'image_id' => ['$first' => '$image_id'],
                            'video_id' => ['$first' => '$video_id'],
                            'channel' => ['$first' => '$channel'],
                            'is_read' => ['$first' => '$is_read'],
                            'is_read_cloak' => ['$first' => '$is_read_cloak'],
                            'is_sender_suspended' => ['$first' => '$is_sender_suspended'],
                            'is_bulk' => ['$first' => '$is_bulk'],
                            'idate' => ['$first' => '$idate'],
                            'cancelled' => ['$first' => '$cancelled'],
                        ],
                    ],
                    ['$sort' => ['idate' => -1]],
                    ['$skip' => $offset],
                    ['$limit' => $limit]
                ]
            );
        });

        return $result;
    }

    /**
     * @param array $favoriteUserIds
     * @param int $userId
     * @param int $limit
     * @param int $offset
     *
     * @return Collection
     */
    public function getConversationMessagesFavorites(array $favoriteUserIds, int $userId, int $limit, int $offset, User $user): Collection
    {
        if (empty($favoriteUserIds)) {
            return collect([]);
        }

        $userEventsIds = EventMembership::select('event_id')
                                        ->with('event')
                                        ->has('event')
                                        ->where('user_id', $userId)
                                        ->get()
                                        ->pluck('event_id')
                                        ->toArray();

        if (!$user->isGhosted()) {
            $query = [
                'is_recipient_ghosted' => ['$in' => [0, null]],
                'is_sender_ghosted' => ['$in' => [0, null]],
            ];
        } else {
            $query = [
                'is_recipient_ghosted' => ['$in' => [0,1]],
                'is_sender_ghosted' => ['$in' => [0,1]],
            ];
        }

        $result = Message::raw(function($collection) use ($favoriteUserIds, $userId, $limit, $offset, $userEventsIds, $query){
            return $collection->aggregate(
                [
                    [
                        '$match' => [
                            '$and' => [
                                [
                                    '$or' => [
                                        [
                                            '$and' => [
                                                ['user_from' => $userId],
                                                ['user_to' => ['$in' => $favoriteUserIds]],
                                                ['deleted_for_user_from' => ['$in' => [0, null]]],
                                            ],
                                        ],
                                        [
                                            '$and' => [
                                                ['user_to' => $userId],
                                                ['user_from' => ['$in' => $favoriteUserIds]],
                                                ['deleted_for_user_to' => ['$in' => [0, null]]],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'is_sender_suspended' => ['$in' => [0, null]],
                                    'is_sender_ghosted' => $query['is_sender_ghosted'],
                                    'is_recipient_ghosted' => $query['is_recipient_ghosted'],
                                    'is_message_blocked_by_user' => ['$in' => [0, null]],
                                    'is_blocked_by_sender' => ['$in' => [0, null]],
                                    'is_blocked_by_recipient' => ['$in' => [0, null]],
                                ],
                                [
                                    '$or' => [
                                        [
                                            '$and' => [
                                                ['is_bulk' => 1],
                                                ['user_from' => ['$ne' => $userId]],
                                            ],
                                        ],
                                        ['is_bulk' => ['$in' => [0, null]]],
                                    ],
                                ],
                                [
                                    '$and' => [
                                        [
                                            '$or' => [
                                                [
                                                    'event_id' => [
                                                        '$in' => $userEventsIds,
                                                        '$exists' => true
                                                    ]
                                                ],
                                                [
                                                    'event_id' => [
                                                        '$exists' => false
                                                    ]
                                                ]
                                            ]
                                        ],
                                    ],
                                ],
                                [
                                    '$or' => [
                                        [
                                            '$and' => [
                                                ['user_to' => $userId]
                                            ],
                                        ],
                                        [
                                            '$and' => [
                                                ['user_from' => $userId]
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    ['$sort' => ['idate' => -1]],
                    [
                        '$group' => [
                            '_id' => '$conversation',
                            'conversation' => ['$first' => '$conversation'],
                            'user_from' => ['$first' => '$user_from'],
                            'user_to' => ['$first' => '$user_to'],
                            'event_id' => ['$first' => '$event_id'],
                            'message' => ['$first' => '$message'],
                            'msg_type' => ['$first' => '$msg_type'],
                            'image_id' => ['$first' => '$image_id'],
                            'video_id' => ['$first' => '$video_id'],
                            'channel' => ['$first' => '$channel'],
                            'is_read' => ['$first' => '$is_read'],
                            'is_read_cloak' => ['$first' => '$is_read_cloak'],
                            'is_sender_suspended' => ['$first' => '$is_sender_suspended'],
                            'is_bulk' => ['$first' => '$is_bulk'],
                            'idate' => ['$first' => '$idate'],
                            'cancelled' => ['$first' => '$cancelled'],
                        ],
                    ],
                    ['$sort' => ['idate' => -1]],
                    ['$skip' => $offset],
                    ['$limit' => $limit]
                ]
            );
        });

        return $result;
    }

    /**
     * @param User $user
     * @return Collection
     */
    public function getAllCachedConversations(User $user)
    {
        return $this->getCachedConversations($user->id, 0, 'all', 999999);
    }

    /**
     * @param int $userId
     * @param Carbon $minDate
     *
     * @return int
     */
    public function getConversationsCount(int $userId): int
    {
        return Message::where(function($q) use($userId) {
            $q->where('user_from', $userId)
                ->orWhere('user_to', $userId);
        })
            ->distinct('conversation')
            ->get()
            ->count();
    }

    /**
     * @param int $userId
     * @param int $page
     * @param string $group
     * @param int $limit
     * @return Collection
     */
    public function getCachedConversations(int $userId, int $page, string $group, int $limit): Collection
    {
        $getAllCachedConversations = collect(json_decode(Redis::get('conversations:' . $userId), true));
        switch ($group) {
            case 'all': {
                $getAllCachedConversations = $getAllCachedConversations->filter(function ($conversation) {
                    return (!isset($conversation['is_message_ghosted']) || $conversation['is_message_ghosted'] === 0)
                        &&
                        (!isset($conversation['conversation_is_blocked']) || $conversation['conversation_is_blocked'] === 0)
                        &&
                        (!isset($conversation['is_message_suspended']) || $conversation['is_message_suspended'] === 0);
                });
                break;
            }
            case 'unread':{
                $getAllCachedConversations = $getAllCachedConversations->filter(function ($conversation) {
                    return $conversation['unreadMessagesCount'] > 0
                        &&
                        (!isset($conversation['is_message_ghosted']) || $conversation['is_message_ghosted'] === 0)
                        &&
                        (!isset($conversation['conversation_is_blocked']) || $conversation['conversation_is_blocked'] === 0)
                        &&
                        (!isset($conversation['is_message_suspended']) || $conversation['is_message_suspended'] === 0);
                });
                break;
            }
            case 'favorites':{
                $favoriteUserIds = (new UserFavoriteRepository)->getAllFavoriteIds($userId);

                $getAllCachedConversations = $getAllCachedConversations->filter(function ($conversation) use ($favoriteUserIds) {
                    return (
                            isset($conversation['interlocutor'])
                            &&
                            in_array($conversation['interlocutor']['id'], $favoriteUserIds)
                            &&
                            $conversation['interlocutor']['isFavorite']
                        )
                        &&
                        $conversation['chatType'] === Message::CHANNEL_USER
                        &&
                        (
                            !isset($conversation['is_message_ghosted'])
                            ||
                            $conversation['is_message_ghosted'] === 0
                        )
                        &&
                        (!isset($conversation['conversation_is_blocked']) || $conversation['conversation_is_blocked'] === 0)
                        &&
                        (!isset($conversation['is_message_suspended']) || $conversation['is_message_suspended'] === 0);
                });
                break;
            }
        }

        foreach ($getAllCachedConversations as $key => $getAllCachedConversation) {
            if ($getAllCachedConversation['chatType'] == 'group') {
                $membership = EventMembership::searchByIds($userId, $getAllCachedConversation['event']['id']);
                $eventUserId = $getAllCachedConversation['event']['user_id'];
                $eventUser = User::find($eventUserId);
                $currentUser = User::find($userId);
                
                if (empty($membership) ||
                    !in_array($membership->status, [
                        EventMembership::STATUS_MEMBER,
                        EventMembership::STATUS_HOST,
                    ]) ||
                    ($eventUserId != $userId && $currentUser->isBlockedBy($eventUser))
                ) {
                    unset($getAllCachedConversations[$key]);
                }
            }
        }

        return $getAllCachedConversations->forPage($page + 1, $limit);
    }

    /**
     * @param int $eventId
     * @param string $type
     * @return mixed
     */
    public function getLastGroupMessage(int $eventId, string $type)
    {
        $query = Message::where('event_id', $eventId)
                        ->where('channel', Message::CHANNEL_GROUP);

        if ($type === User::STATUS_GHOSTED) {
            $query->where('is_sender_ghosted', '!=', 1);
        }

        return $query->orderByDesc('idate')->latest()->first();
    }
}
