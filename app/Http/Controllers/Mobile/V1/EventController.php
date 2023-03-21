<?php

namespace App\Http\Controllers\Mobile\V1;

use App\EventInvitation;
use App\Http\Requests\EventHandleInvitationRequest;
use App\Http\Requests\InviteEventMemberRequest;
use App\Services\ChatService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

use App\Event;
use App\Message;
use App\EventMembership;
use App\Events\EventMembershipUpdated;
use App\Models\Event\EventReport;
use App\Repositories\EventRepository;
use App\Services\EventMembershipService;
use App\Services\EventService;
use App\User;

class EventController extends Controller
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
     * @return JsonResponse
     */
    public function stickyEvents(): JsonResponse
    {
        $validator = Validator::make(request()->all(), [
            'except' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        $params = request()->all();

        $eventService = new EventService();
        $eventService->setCurrentUser(request()->user());

        $eventService->setExcept($params['except'] ?? []);

        $result = $eventService->getStickyEvents();

        return response()->json($result);
    }

    /**
     * @return JsonResponse
     */
    public function eventsAround(): JsonResponse
    {
        $eventTypes = [
            Event::TYPE_GUIDE,
            Event::TYPE_BANG,
            Event::TYPE_FUN
        ];

        $validator = Validator::make(request()->all(), [
            'type' => 'required|string|in:' . implode(',', $eventTypes),
            'date' => 'nullable|date_format:Y-m-d',
            'except' => 'nullable|array',
            'page' => 'required|integer',
            'limit' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        $params = request()->all();

        $currentDate = $params['date'] ?? null;

        $eventService = new EventService();
        $eventService->setCurrentUser(request()->user());
        $eventService->setFilterType($params['type']);
        $eventService->setPage($params['page']);
        $eventService->setLimit($params['limit']);

        $eventService->setExcept($params['except'] ?? []);
        $eventService->setDate($currentDate);

        $result = $eventService->getEvents();

        return response()->json($result);
    }

    /**
     * Report Event
     *
     * @return JsonResponse
     */
    public function reportEvent(): JsonResponse
    {
        /** @var Event $event */
        $event = Event::find(request()->get('id'));
        if (!$event) {
            return response()->json([
                'success' => false,
                'trans'   => 'events.not_found',
            ]);
        }

        $reasons = request()->get('reason');
        $userId = auth()->user()->id;

        if (is_array($reasons)) {
            foreach ($reasons as $reason) {
                $event->reportByUser($userId, $reason);
            }
        } else {
            $event->reportByUser($userId, $reasons);
        }

        return response()->json([
            'success' => true,
            'trans'   => 'events.report_success',
        ]);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function eventInfo($id): JsonResponse
    {
        $currentUser = request()->user();

        /** @var Event $event */
        $event = Event::where('id', $id)
                      ->with(['likes' => function ($query) {
                          $query->with('userActive')
                                ->whereHas('userActive', function ($query) {
                                    $query->where('users.status', User::STATUS_ACTIVE);
                                });
                      }])->first();

        if (!empty($event)) {
            $response = $event->getAttributesByMode(Event::ATTRIBUTES_MODE_FULL, $currentUser);

            return response()->json($response);
        } else {
            return response()->json('Event not found', 404);
        }
    }

    /**
     * @return JsonResponse
     */
    public function createEvent(): JsonResponse
    {
        $request = request();
        $eventRepository = new EventRepository();

        // Get event type
        $type = $request->get('type', Event::TYPE_FUN);

        // Verify user is active
        $currentUser = $request->user();
        if (!$currentUser->isPro() && $type !== Event::TYPE_GUIDE) {
            $limit = config('const.FREE_EVENTS_LIMIT');
            $eventsAlready = Event::where('user_id', $currentUser->id)->count();
            if ($eventsAlready >= $limit) {
                return response()->json([
                    'error' => 'For more events, upgrade to PRO'
                ], 422);
            }
        }

        // Validate request data
        $data = $request->all();

        // Pre-process the payload
        $data = $this->transformData($data);

        $validatorRules = null;

        if ($type == Event::TYPE_BANG) {
            $validatorRules = Event::BANG_FIELDS_RULES;
        } else if ($type == Event::TYPE_FUN || $type === Event::TYPE_FRIENDS) {
            $validatorRules = Event::EVENT_FIELDS_RULES;
        } else if ($type == Event::TYPE_GUIDE) {
            $validatorRules = Event::GUIDE_FIELDS_RULES;
        }

        $validator = Validator::make($data, $validatorRules);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        // Create an event
        switch ($type) {
            case Event::TYPE_FUN:
            case Event::TYPE_FRIENDS:
            case Event::TYPE_GUIDE:
            case Event::TYPE_BANG: {
                $event = $eventRepository->safeCreate($currentUser, $data);
                break;
            }
            default: {
                return response()->json([
                    'error' => 'Unknown event type'
                ], 422);
            }
        }

        if ($type === Event::TYPE_BANG) {
            $this->addAdminToBang([
                'eventId' => $event->id,
                'userId'  => $currentUser->id,
                'action'  => EventMembership::ACTION_HOST_ACCEPT
            ], $event);
        }

        return response()->json([
            'event' => [
                Event::ATTRIBUTES_MODE_FULL     => $event->getAttributesByMode(Event::ATTRIBUTES_MODE_FULL, $currentUser),
                Event::ATTRIBUTES_MODE_GENERAL  => $event->getAttributesByMode(Event::ATTRIBUTES_MODE_GENERAL, $currentUser),
                Event::ATTRIBUTES_MODE_DISCOVER => $event->getAttributesByMode(Event::ATTRIBUTES_MODE_DISCOVER, $currentUser),
            ]
        ], 200);
    }

    /**
     * @param $bangData
     * @return bool|JsonResponse
     */
    public function addAdminToBang($bangData, Event $event): bool
    {
        $membershipService = new EventMembershipService();
        $membershipService->setAction($bangData['action']);
        $membershipService->setEvent($event);
        $membershipService->setEventId($bangData['eventId']);
        $membershipService->setCurrentUser(request()->user());

        $membershipService->updateStatus();

        return true;
    }

    /**
     * @return JsonResponse
     */
    public function updateEvent(int $id): JsonResponse
    {
        $request = request();
        $eventRepository = new EventRepository();
        $currentUser = $request->user();

        $event = $eventRepository->findEvent($id);
        if (empty($event)) {
            return response()->json([
                'error' => 'Event not found'
            ], 422);
        }

        if ($event->user_id != $currentUser->id) {
            return response()->json([
                'error' => 'You are not allowed to update this event'
            ], 422);
        }

        // Validate request data
        $data = $request->all();

        // Pre-process the payload
        $data = $this->transformData($data);

        array_walk_recursive($data, function (&$entry) {
            $entry = mb_convert_encoding($entry, 'UTF-8');
        });

        $rules = Event::getNonStrictValidationRules($event->type);
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        $event = $eventRepository->safeUpdate($currentUser, $event, $data);

        return response()->json([
            'event' => [
                Event::ATTRIBUTES_MODE_FULL     => $event->getAttributesByMode(Event::ATTRIBUTES_MODE_FULL, $currentUser),
                Event::ATTRIBUTES_MODE_GENERAL  => $event->getAttributesByMode(Event::ATTRIBUTES_MODE_GENERAL, $currentUser),
                Event::ATTRIBUTES_MODE_DISCOVER => $event->getAttributesByMode(Event::ATTRIBUTES_MODE_DISCOVER, $currentUser),
            ]
        ], 200);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function removeEvent(int $id): JsonResponse
    {
        $currentUser = request()->user();
        $event = Event::where('id', $id)->first();
        if (empty($event)) {
            response()->json([
                'error' => 'Event not found'
            ], 422);
        }

        $count = (new EventRepository)->deleteEvent($id, $currentUser->id);
        if ($count) {

            if ($event->type === Event::TYPE_BANG) {
                $recipientsIds = EventMembership::select('user_id')
                    ->where('event_id', $id)
                    ->get()
                    ->toArray();
            } else {
                $recipientsIds = Message::select('user_from', 'user_to')
                    ->project(['_id' => 0])
                    ->where('event_id', $id)
                    ->get()
                    ->toArray();
            }

            $usersIds = [];

            foreach ($recipientsIds as $recipientsId) {
                $usersIds = array_merge($usersIds, array_values($recipientsId));
            }

            $channel = $event->type !== Event::TYPE_BANG ? Message::CHANNEL_EVENT : Message::CHANNEL_GROUP;

            (new ChatService())->removeCacheConversationForEventOrGroupRecipients($channel, $currentUser->id, array_unique($usersIds), $id);

            Message::where('event_id', $id)->delete();
            EventReport::where('event_id', $id)->delete();
            EventMembership::where('event_id', $id)->delete();

            // Send an "event was removed" signal to members (except host)
            if ($event->type == Event::TYPE_BANG) {
                $broadcastingEvent = new EventMembershipUpdated([
                    'ignore_recipient_id' => [
                        $event->user_id
                    ],
                    'event_id' => $event->id,
                    'event' => null,
                    'action' => EventMembership::ACTION_REMOVE_EVENT
                ]);
                event($broadcastingEvent);
            }

            return response()->json($count);
        } else {
            return response()->json($count, 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function updateMembership(): JsonResponse
    {
        $currentUser = request()->user();
        $data = request()->all();

        $allActions = [
            EventMembership::ACTION_REQUEST,
            EventMembership::ACTION_ACCEPT,
            EventMembership::ACTION_HOST_ACCEPT,
            EventMembership::ACTION_REJECT,
            EventMembership::ACTION_LEAVE,
            EventMembership::ACTION_REMOVE,
        ];

        // Validate request
        $validator = Validator::make($data, [
            'eventId' => 'required|integer|exists:events,id',
            'userId' => 'nullable|integer|exists:users,id',
            'action' => 'required|in:' . implode(',', $allActions),
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $action = $data['action'];

        /** @var Event $event */
        $event = Event::find($data['eventId']);

        /** @var EventMembership $currentUserMembership */
        $currentUserMembership = EventMembership::search($currentUser, $event);

        if (!empty($data['userId'])) {
            $user = User::find($data['userId']);
            if (empty($user)) {
                return response()->json([
                    'error' => 'User not found'
                ], 422);
            }
            $membership = EventMembership::search($user, $event);
        }

        $status = $currentUserMembership->status ?? null;
        switch ($status) {
            case EventMembership::STATUS_HOST: {
                $allowedActions = [
                    EventMembership::ACTION_ACCEPT,
                    EventMembership::ACTION_REJECT,
                    EventMembership::ACTION_REMOVE
                ];
                break;
            }
            case EventMembership::STATUS_MEMBER:
            case EventMembership::STATUS_REQUESTED: {
                $allowedActions = [EventMembership::ACTION_LEAVE];
                break;
            }
            case null:
            case EventMembership::STATUS_LEAVED: {
                $allowedActions = [EventMembership::ACTION_REQUEST];
                break;
            }
            case EventMembership::STATUS_REMOVED:{
                return response()->json(['error' => 'You was removed this bang\'s members list'], 422);
                break;
            }
            default: {
                return response()->json(['error' => 'Unknown membership action'], 422);
            }
        }

        if (!in_array($action, $allowedActions)) {
            return response()->json(['error' => 'This action is not allowed'], 422);
        }

        $membershipService = new EventMembershipService();
        $membershipService->setAction($action);
        $membershipService->setEvent($event);
        $membershipService->setEventId($data['eventId']);
        $membershipService->setUser($user ?? null);
        $membershipService->setMembership($membership ?? null);
        $membershipService->setCurrentUser($currentUser);
        $membershipService->setCurrentUserMembership($currentUserMembership ?? null);

        if (!empty($currentUser) && !empty($action)) {
            if ($action == EventMembership::ACTION_LEAVE) {
                EventInvitation::where('event_id', $event->id)
                    ->where('user_id', $currentUser->id)
                    ->update([
                        'status' => EventInvitation::STATUS_REJECTED,
                    ]);
            }
        }

        $membershipService->updateStatus();

        return response()->json([
            'event' => [
                Event::ATTRIBUTES_MODE_FULL     => $event->getAttributesByMode(Event::ATTRIBUTES_MODE_FULL, $currentUser),
                Event::ATTRIBUTES_MODE_GENERAL  => $event->getAttributesByMode(Event::ATTRIBUTES_MODE_GENERAL, $currentUser),
                Event::ATTRIBUTES_MODE_DISCOVER => $event->getAttributesByMode(Event::ATTRIBUTES_MODE_DISCOVER, $currentUser),
            ]
        ], 200);
    }

    protected function transformData(array $data): array
    {
        if (isset($data['chemsfriendly'])) {
            $data['chemsfriendly'] = filter_var($data['chemsfriendly'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($data['is_profile_linked'])) {
            $data['is_profile_linked'] = filter_var($data['is_profile_linked'], FILTER_VALIDATE_BOOLEAN);
        }

        if (empty($data['time'])) {
            $data['time'] = '';
        }

        $data['is_private'] = (boolean) (($data['is_private'] ?? false) ? true : false);

        return $data;
    }

    public function inviteMembers(InviteEventMemberRequest $request)
    {
        /** @var User $currentUser */
        $currentUser = request()->user();

        /** @var Event $event */
        $event = $currentUser
            ->events()
            ->where('id', $request->eventIdComputed)
            ->firstOrFail();

        if (empty($request->selectedMembers) || !count($request->selectedMembers)) {
            throw new \Exception('selected memebers is empty');
        }

        foreach ($request->selectedMembers as $selectedMember) {
            /*
             * Check member
             */
            $checkExists = $event->members()
                ->where('user_id', $selectedMember)
                ->first();

            if (null !== $checkExists) {
                continue;
            }

            /*
             * Check invitation
             */
            $checkExistsInvitation = EventInvitation::where('event_id', $event->id)
                ->where('user_id', $selectedMember)
                ->first();

            if (null !== $checkExistsInvitation) {
                continue;
            }

            $eventInvitation = EventInvitation::create([
                'invited_by_user_id' => $currentUser->id,
                'user_id' => $selectedMember,
                'event_id' => $event->id,
            ]);
        }

        return response('ok');
    }

    /**
     * @param Request $request
     * @param string $type
     */
    public function handleInvitation(EventHandleInvitationRequest $request, string $type)
    {
        /** @var User $currentUser */
        $currentUser = request()->user();

        /** @var EventInvitation $eventInvitation */
        $eventInvitation = EventInvitation::where('user_id', $currentUser->id)
            ->where('status', EventInvitation::STATUS_PENDING)
            ->where('event_id', $request->eventId)
            ->firstOrFail();

        switch ($type) {
            case "accept":
                $eventInvitation->status = EventInvitation::STATUS_ACCEPTED;
                $eventInvitation->save();

                EventMembership::create([
                    'event_id' => $eventInvitation->event_id,
                    'user_id' => $currentUser->id,
                    'status' => EventMembership::STATUS_MEMBER,
                ]);
                break;

            case "decline":
                $eventInvitation->status = EventInvitation::STATUS_REJECTED;
                $eventInvitation->save();
                break;

            default:
                throw new \Exception('unkown type');
        }

        return response('ok');
    }
}
