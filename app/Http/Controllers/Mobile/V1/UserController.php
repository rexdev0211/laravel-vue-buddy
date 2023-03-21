<?php

namespace App\Http\Controllers\Mobile\V1;

use App\EventInvitation;
use App\EventMembership;
use App\Events\UserBlocked;
use App\Newsletter;
use App\OnesignalPlayer;
use App\Enum\UserReportTypes;
use App\Repositories\CountryRepository;
use App\Services\ChatService;
use App\Services\SpamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use App\User;
use App\UserPhoto;
use App\Event;
use App\Events\RefreshDataRequest;
use App\Events\UserEvent;
use App\Services\BackendService;
use App\Repositories\NewsletterRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\UserBlockedRepository;
use App\Repositories\UserFavoriteRepository;
use App\Repositories\PhotoRepository;
use App\Repositories\UserReportedRepository;
use App\Repositories\UserRepository;
use App\Repositories\VideoRepository;
use App\Services\MediaService;

class UserController extends Controller
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
    public function currentUserInfo(): JsonResponse
    {
        $backendService = new BackendService();
        $userPhotoRepository = new PhotoRepository();
        $userRepository = new UserRepository();
        $notificationRepository = new NotificationRepository();
        $userVideoRepository = new VideoRepository();
        $spamService = new SpamService();

        /** @var User $currentUser */
        $currentUser = request()->user();
        $spamService->setUser($currentUser);

        //if user is suspended/deleted and is not admin
        if (
            $currentUser->isSuspended() || $currentUser->isDeleted()
            and
            !\Auth::guard('admin')->check()
        ) {
            return response()->json([
                'user' => [
                    "status" => $currentUser->status,
                    'deleted' => $currentUser->isDeleted(),
                ]
            ]);
        }

        //update user online status
        if (
            !request()->hasHeader('X-Login-As')
            &&
            !request()->hasHeader('x-login-as')
        ) {
            $currentUser = $userRepository->updateUserStatus($currentUser);
        }
        $returnUser = $currentUser->getAllAttributes();

        //options
        $heights = $backendService->getPredefinedHeights();
        $weights = $backendService->getPredefinedWeights();
        $bodyTypes = $backendService->getPredefinedBodyTypes();
        $penisSizes = $backendService->getPredefinedPenisSizes();
        $positionTypes = $backendService->getPredefinedPositionTypes();
        $hivTypes = $backendService->getPredefinedHivTypes();
        $drugsTypes = $backendService->getPredefinedDrugsTypes();

        $defaultPhotos = [
            'photo' => null,
            'photo_orig' => UserPhoto::DEFAULT_IMAGE_ORIGINAL,
            'photo_small' => UserPhoto::DEFAULT_IMAGE_SMALL,
        ];

        $options = [
            'heights' => $heights,
            'weights' => $weights,
            'bodyTypes' => $bodyTypes,
            'penisSizes' => $penisSizes,
            'positionTypes' => $positionTypes,
            'hivTypes' => $hivTypes,
            'drugsTypes' => $drugsTypes,
            'defaultPhotos' => $defaultPhotos
        ];

        //photos
        $photos = $userPhotoRepository->getGalleryPhotosByUserId($currentUser->id);
        foreach ($photos as $photo) {
            /** @var UserPhoto $photo */
            $photo->setUrls(true);
        }

        //online favorites
        $onlineFavorites = $userRepository->getOnlineFavorites($currentUser);

        //my taps
        $myTaps = $notificationRepository->getLastDayUserWaves($currentUser);

        //events
        $myEvents = $currentUser->events->map(function ($event) use ($currentUser){
            /** @var Event $event */
            return $event->getAttributesByMode(Event::ATTRIBUTES_MODE_DISCOVER, $currentUser);
        });

        $error = null;
        try {
            $attendedEvents = $currentUser->eventMemberships()
                ->where('event_members_map.status', EventMembership::STATUS_MEMBER)
                ->get()
                ->filter(function($entry) use ($currentUser){
                    if (empty($entry->event)){
                        return false;
                    }
                    if ($currentUser->id != $entry->event->user_id && $currentUser->isBlockedBy(User::find($entry->event->user_id))) {
                        return false;
                    }
                    $dateParsed = Carbon::parse($entry->event->event_date);
                    return $dateParsed->gte(Carbon::yesterday());
                })
                ->map(function ($entry) use ($currentUser){
                    return $entry->event->getAttributesByMode(
                        Event::ATTRIBUTES_MODE_DISCOVER,
                        $currentUser
                    );
                });
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $attendedEvents = [];
        }

        $allEvents = collect($myEvents)
            ->merge($attendedEvents)
            ->sortBy('date')
            ->values();

        //clubs
        $myClubs = $currentUser->clubs->map(function ($event) use ($currentUser){
            /** @var Event $event */
            return $event->getAttributesByMode(Event::ATTRIBUTES_MODE_DISCOVER, $currentUser);
        });

        //videos
        $videos = $userVideoRepository->getGalleryVideosByUserId($currentUser->id);
        foreach ($videos as $video) {
            $video->setUrls(true);
        }

        // Membership requests (other users > my events)
        $myEventIds = collect($myEvents)
            ->filter(function($event){
                $dateParsed = Carbon::parse($event['date']);
                return $dateParsed->gte(Carbon::yesterday());
            })
            ->pluck('id')
            ->toArray();

        try {
            $membershipRequests = EventMembership::whereIn('event_id', $myEventIds)
                ->where('status', EventMembership::STATUS_REQUESTED)
                ->select(['event_id'])
                ->groupBy('event_id')
                ->get()
                ->map(function($value){
                    return $value['event_id'];
                })
                ->toArray();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $membershipRequests = [];
        }

        $returnUser['has_event_notifications'] = !empty($membershipRequests);
        
        // Membership requests for clubs
        $myClubIds = collect($myClubs)
            ->pluck('id')
            ->toArray();

        try {
            $membershipRequestsForClubs = EventMembership::whereIn('event_id', $myClubIds)
                ->where('status', EventMembership::STATUS_REQUESTED)
                ->select(['event_id'])
                ->groupBy('event_id')
                ->get()
                ->map(function($value){
                    return $value['event_id'];
                })
                ->toArray();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $membershipRequestsForClubs = [];
        }

        $returnUser['has_club_notifications'] = !empty($membershipRequestsForClubs);

        try {
            $activeMemberships = $currentUser
                ->activeEventMemberships()
                ->with('event')
                ->get()
                ->filter(function($entry){
                    if (empty($entry->event)){
                        return false;
                    }
                    $dateParsed = Carbon::parse($entry->event->event_date);
                    return $dateParsed->gte(Carbon::yesterday());
                })
                ->map(function($value){
                    return $value['event_id'];
                })
                ->values();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $activeMemberships = [];
        }

        $widgetAnnounce = $currentUser->widget_announce;
        if (!$currentUser->widget_announce) {
            $currentUser->widget_announce = 'announced';
            $currentUser->save();
        }

        /*
         * Invitations to events
         */
        $invitationsToBang = EventInvitation::with(['event'])
            ->where('user_id', $currentUser->id)
            ->where('status', EventInvitation::STATUS_PENDING)
            ->get()
            ->map(function(EventInvitation $invitation) {
                $invitedByUser = $invitation->invitedByUser()->first()->only([
                    'id',
                    'name',
                ]);

                $invitation->invitedByUser = $invitedByUser;

                return $invitation;
            })->filter(function(EventInvitation $invitation) {
                /** @var Event $event */
                $event = Event::find($invitation->event_id);

                if (null === $event) {
                    return false;
                }

                if ($event && Carbon::parse($event->event_date.' 23:59:59')->lessThan(now())) {
                    return false;
                }

                if ($event && $event->type == Event::TYPE_CLUB) {
                    return false;
                }
                return true;
            });

        if ($invitationsToBang->count() > 0) {
            $invitationsToBang = $invitationsToBang->values();
        }

        /*
         * Invitations to clubs
         */
        $invitationsToClub = EventInvitation::with(['event'])
            ->where('user_id', $currentUser->id)
            ->where('status', EventInvitation::STATUS_PENDING)
            ->get()
            ->map(function(EventInvitation $invitation) {
                $invitedByUser = $invitation->invitedByUser()->first()->only([
                    'id',
                    'name',
                ]);

                $invitation->invitedByUser = $invitedByUser;

                return $invitation;
            })->filter(function(EventInvitation $invitation) {
                /** @var Event $event */
                $event = Event::find($invitation->event_id);

                if (null === $event) {
                    return false;
                }

                if ($event && $event->type != Event::TYPE_CLUB) {
                    return false;
                }
                return true;
            });

        if ($invitationsToClub->count() > 0) {
            $invitationsToClub = $invitationsToClub->values();
        }

        return response()->json([
            'error' => $error,
            'user' => $returnUser,
            'onlineFavorites' => $onlineFavorites,
            'options' => $options,
            'photos' => $photos,
            'videos' => $videos,
            'myTaps' => $myTaps,
            'myEvents' => $allEvents,
            'activeMemberships' => $activeMemberships,
            'membershipRequests' => $membershipRequests,
            'favoritesCount' => $currentUser->getFavouritesCount(),
            'blockedCount' => $currentUser->getBlockedCount(),
            'blockedUsersIds' => $currentUser->getBlockedUsersIds(),
            'latestWidget' => $currentUser->latest_widget,
            'widgetAnnounce' => $widgetAnnounce,
            'invitationsToBang' => $invitationsToBang,
            'invitationsToClub' => $invitationsToClub,
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getFavourites(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = request()->user();

        /** @var User $favouritesObjects */
        $favouritesObjects = $currentUser->favorites()->with(['favorited'])->get();
        $favourites = [];

        /** @var User $favouritesObject */
        foreach ($favouritesObjects as $favouritesObject) {
            $favoritedUser = $favouritesObject->favorited;

            if (!empty($favoritedUser->deleted_at)) {
                continue;
            }

            $favorited = $favoritedUser->getPublicAttributes();
            $favourites[] = $favorited;
        }

        return response()->json($favourites);
    }

    /**
     * @return JsonResponse
     */
    public function getUserStatus(): JsonResponse
    {
        $currentUser = request()->user();

        $userRepository = new UserRepository();
        if (
            !request()->hasHeader('X-Login-As')
            &&
            !request()->hasHeader('x-login-as')
        ) {
            /** @var User $currentUser */
            $currentUser = $userRepository->updateUserStatus(request()->user());
        }

        //get users status
        $userIds = request()->get('ids');

        $freshUsers = [];
        if (!empty($userIds)) {
            $users = $userRepository->getUsersStatuses($userIds, $currentUser->lat, $currentUser->lng);
            foreach ($users as $user) {
                /** @var User $user */
                $freshUsers[] = $user->getAttributesByMode(User::ATTRIBUTES_MODE_STATUS, $currentUser);
            }
        }

        $response = [
            'user'            => $currentUser->getAllAttributes(),
            'freshUsers'      => $freshUsers,
            'onlineFavorites' => $userRepository->getOnlineFavorites($currentUser),
            'isOnline'        => $currentUser->isOnline(),
            'discreet_mode'   => $currentUser->discreet_mode,
            'invisible'       => $currentUser->invisible,
            'pro'             => $currentUser->isPro(),
        ];

        return response()->json($response);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function userInfo($id): JsonResponse
    {
        $currentUser = request()->user();

        $user = (new UserRepository)->findUser($id);

        if (null === $user || $user->isBlockedBy($currentUser)) {
            return response()->json(['error' => "Can't view profile of blocked user"]);
        }

        return response()->json(
            $user->getPublicAttributes($currentUser)
        );
    }

    /**
     * @param int $photoId
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function userPhoto(int $photoId, int $userId): JsonResponse
    {
        $photo = (new PhotoRepository)->findUserPhoto($userId, $photoId);
        $photo->setUrls($userId == request()->user()->id);
        return response()->json($photo->toArray());
    }

    /**
     * @return JsonResponse
     */
    public function userFavorite(int $userId=null, int $newFavorite=null): JsonResponse
    {
        if (null !== $userId && null !== $newFavorite) {
            $data = [];
            $data['favourite'] = $newFavorite;
            $data['userId'] = $userId;
        } else {
            $data = request()->all();

            // Validate request
            $validator = Validator::make($data, [
                'userId' => 'required|integer|exists:users,id',
                'favourite' => 'required|boolean'
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }
        }

        $userFavoriteRepository = new UserFavoriteRepository();
        $chatService = new ChatService();
        $currentUserId = request()->user()->id;

        /** @var User $user */
        $user = request()->user();

        /** @var array $blockedIds */
        $blockedIds = $user->blocked()->get()->pluck('user_blocked_id');
        $favouritesCountExcludeBlocked = $user->favorites()->whereNotIn('user_favorite_id', $blockedIds)->count();

        if (
            !$user->isPro()
            &&
            $data['favourite']
            &&
            $favouritesCountExcludeBlocked >= config('const.FREE_FAVORITES_LIMIT')
        ) {
            return response()->json([
                'success' => false,
                'proRequired' => true,
            ]);
        }

        if ($data['favourite']) {
            $userFavoriteRepository->addFavorite($currentUserId, $data['userId']);
            $isFavorite = true;
        } else {
            $userFavoriteRepository->removeFavorite($currentUserId, $data['userId']);
            $isFavorite = false;
        }

        $chatService->favoritesControl($currentUserId, $data['userId'], $isFavorite);

        event(new RefreshDataRequest($currentUserId, 'userFavorite'));

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function blockUser(int $id=null): JsonResponse
    {
        if (!empty($id)) {
            $data = [];
            $data['userId'] = $id;
        } else {
            $data = request()->all();
        }

        $currentUser = request()->user();

        // Validate request
        $validator = Validator::make($data, [
            'userId' => 'required|integer|exists:users,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        if (
            !$currentUser->isPro()
            &&
            $currentUser->blocked->count() >= config('const.FREE_BLOCKS_LIMIT')
        ){
            return response()->json([
                'success' => false,
                'proRequired' => true,
            ]);
        }

        /** @var User $user */
        $user = User::find($data['userId']);
        $user->block($currentUser);

        (new ChatService())->setMessageIsBlockedOrUnblocked(request()->user()->id, $data['userId'], 'block');

        event(new UserBlocked(auth()->user()->id, $data['userId'], 'block'));

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function unblockUsers(): JsonResponse
    {
        $user = request()->user();
        $blockedUsersIds = (new UserBlockedRepository)->resetBlockedUsers($user->id);
        $chatService = new ChatService();

        foreach ($blockedUsersIds as $blockedUserId) {
            $chatService->setMessageIsBlockedOrUnblocked($user->id, $blockedUserId, 'unblock');;
        }

        return response()->json('ok');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getBlockedUsers(Request $request): JsonResponse
    {
        $data = $request->all();
        $userId = auth()->user()->id;

        $validator = Validator::make($data, [
            'limit' => 'required',
            'page' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        $limit = (int) $data['limit'];
        $page = (int) $data['page'];

        $userRepository = new UserRepository();
        $blockedUsers = $userRepository->getBlockedUsers($userId, $page, $limit);

        return response()->json($blockedUsers);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function unblockUser(Request $request): JsonResponse
    {
        $currentUserId = auth()->user()->id;
        $data = $request->all();

        (new UserBlockedRepository())->resetBlockedUser($currentUserId, $data['blockedUserId']);

        $unblockedConversations = (new ChatService())->setMessageIsBlockedOrUnblocked($currentUserId, $data['blockedUserId'], 'unblock');

        if (!empty($unblockedConversations['interlocutorConversations'])) {
            event(new UserBlocked($currentUserId, $data['blockedUserId'], 'unblock', $unblockedConversations['interlocutorConversations']));
        }

        if (!empty($unblockedConversations['currentUserConversations'])) {
            event(new UserBlocked($data['blockedUserId'], $currentUserId, 'unblock', $unblockedConversations['currentUserConversations']));
        }

        return response()->json('ok');
    }

    /**
     * @return JsonResponse
     */
    public function updateUser(): JsonResponse
    {
        $request = request();

        $refreshDataRequired = false;
        $user = $request->user();

        $beforeDate = Carbon::today()->subYears(config('const.MINIMUM_AGE'))->format('Y-m-d');

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255|unique:users,name,' . $user->id,
            'email' => 'email|max:255|unique:users,email,' . $user->id,
            'dob' => 'date_format:"Y-m-d"|before_or_equal:"' . $beforeDate . '"'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        //check spam in about-me text
        if ($request->exists('about')) {
            $about = $request->get('about');

            $spamService = new SpamService;
            $spamService->setScope(SpamService::SCOPE_PROFILE);
            $spamService->setUser($user);
            $spamService->setContent($about);
            $suspended = $spamService->userSuspendAttempt();
            if ($suspended) {
                return response()->json([
                    'error' => 'Such description is not allowed'
                ], 422);
            }
        }

        // TODO: make confirmation and verification for email and password modification
        $values = [
            'dob',
            'weight',
            'height',
            'body',
            'position',
            'penis',
            'hiv',
            'drugs',
            'name',
            'address',
            'about',
            'email',
            'password',
            'lat',
            'lng',
            'show_age',
            'unit_system',
            'email_reminders',
            'location_type',
            'locality',
            'state',
            'country_code',
            'address_lat',
            'address_lng',
            'language',

            'has_notifications',
            'has_new_notifications',
            'has_new_visitors',
            'has_new_messages',

            'subscribed',
            'notification_sound',
            'push_notifications',
            'view_sensitive_events',
            'view_sensitive_media',
            'app_view_sensitive_events',
            'app_view_sensitive_media',
            'web_view_sensitive_content',
            'invisible',

            'is_guide_modal_shown',
        ];

        if (auth()->user()->location_type == 'automatic') {
            if (
                request()->hasHeader('X-Login-As')
                ||
                request()->hasHeader('x-login-as')
            ) {
                $values = array_diff($values, ['address', 'lat', 'lng', 'locality', 'state', 'country_code', 'address_lat', 'address_lng']);
            }
        }

        $updateData = [];

        $spamService = new SpamService;

        foreach ($values as $value) {
            if ($request->exists($value)) {
                if ($value == 'password') {
                    $updateData[$value] = bcrypt($request->get($value));
                } else {
                    $updateData[$value] = $request->get($value);

                    if ($value == 'show_age') {
                        $updateData[$value] = $updateData[$value] == 'yes' || (int) $updateData[$value] == 1
                            ? 'yes'
                            : 'no';
                    }

                    if ($value == 'about') {
                        $updateData[$value] = $spamService->replaceRestrictedWords($updateData[$value]);
                    }
                }

                if ($value === 'app_view_sensitive_events') {
                    $updateData['view_sensitive_events'] = (string) $request->get($value);
                } else if ($value === 'app_view_sensitive_media') {
                    $updateData['view_sensitive_media'] = (string) $request->get($value);
                }

                if ($request->has('old_password') && $request->has('new_password')) {
                    $currentUser = request()->user();

                    $validator = Validator::make($request->all(), [
                        'old_password' => 'required|min:6',
                        'new_password' => 'required|confirmed|min:6'
                    ]);

                    if ($validator->fails()) {
                        return response()->json([
                            'error' => $validator->errors()->first()
                        ], 422);
                    }

                    if (\Hash::check($request->get('old_password'), $currentUser->password)) {
                        (new UserRepository())->update($currentUser->id, [
                            'password' => \Hash::make($request->get('new_password'))
                        ]);
                        return response()->json('ok');
                    }

                    return response()->json([
                        'error' => 'wrong_old_password'
                    ], 422);
                }

                if ($user->isPro()) {
                    if (in_array($value, ['invisible'])) $updateData[$value] = (int) $request->get($value);
                }

                if (in_array($value, ['app_view_sensitive_events', 'app_view_sensitive_media'])) {
                    if (!$refreshDataRequired && $updateData[$value] !== $user->{$value}) $refreshDataRequired = true;
                }
            }
        }

        if (
            !request()->hasHeader('X-Login-As')
            &&
            !request()->hasHeader('x-login-as')
        ) {
            if ($request->exists('country_code')) {
                $updateData['country'] = (new CountryRepository())->getCountryNameByCode($request->get('country_code'), $request->get('country'));
            }
        }

        $newsletterRepository = new NewsletterRepository();
        if (count($updateData)) {
            if (isset($updateData['lat'])) {
                $updateData['lat'] = (float) $updateData['lat'];
            }

            if (isset($updateData['lng'])) {
                $updateData['lng'] = (float) $updateData['lng'];
            }
            
            $newUser = (new UserRepository())->updateUser($user->id, $updateData);
            // $currentUser = \Auth::user();
            // $currentUser = $newUser;
            // $currentUser->save();

            $newsletterFields = ['name', 'email', 'language', 'subscribed', 'country_code'];
            foreach ($newsletterFields as $newsletterField) {
                if (isset($updateData[$newsletterField])) {
                    $newsletterRepository->createOrUpdateUserNewsletter($newUser, $user->email);
                    break;
                }
            }

            if ($refreshDataRequired)
                event(new RefreshDataRequest(auth()->user()->id, 'updateUser'));
        }

        return response()->json('ok');
    }

    /**
     * @return JsonResponse
     */
    public function changePassword(): JsonResponse
    {
        $request = request();
        $currentUser = request()->user();

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|min:6',
            'new_password' => 'required|confirmed|min:6'
        ]);

        if ($validator->fails()){
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        if (\Hash::check($request->get('old_password'), $currentUser->password)) {
            (new UserRepository())->update($currentUser->id, [
                'password' => \Hash::make($request->get('new_password'))
            ]);
            return response()->json('ok');
        }

        return response()->json([
            'error' => 'wrong_old_password'
        ], 422);
    }

    /**
     * @return JsonResponse
     */
    public function accountDelete(): JsonResponse
    {
        $currentUser = request()->user();
        $suffix = '2';
        $userPhotoRepository = new PhotoRepository();

        $mediaService = new MediaService();
        foreach ($currentUser->photos as $photo) {
            $mediaService->addSuffixToPhotoName($photo->photo, 'users', $suffix);
            $userPhotoRepository->updatePhoto($photo->id, ['photo' => $photo->photo . $suffix]);
        }

        $delete = (new UserRepository)->softDeleteUserById($currentUser->id);
        if ($delete) {
            event(new UserEvent(['user_to' => $currentUser->id, 'event' => 'delete']));
        }

        return response()->json('ok');
    }

    /**
     * @return JsonResponse
     */
    public function accountDeactivate(): JsonResponse
    {
        $currentUser = request()->user();
        $status = 'deactivated';
        (new UserRepository)->updateUser($currentUser->id, ['status' => $status]);

        /** @var Newsletter $newsletters */
        $newsletters = Newsletter::where('email', $currentUser->email)->get();

        /** @var Newsletter $newsletter */
        foreach ($newsletters as $newsletter) {
            $newsletter->subscribed = 'no';
            $newsletter->save();
        }

        return response()->json($status);
    }

    /**
     * @return JsonResponse
     */
    public function accountActivate(): JsonResponse
    {
        $currentUser = request()->user();
        $status = 'active';
        (new UserRepository)->updateUser($currentUser->id, ['status' => $status]);

        return response()->json($status);
    }

    /**
     * @return JsonResponse
     */
    public function reportUser(int $reportUserId=null): JsonResponse
    {
        if (!empty($reportUserId)) {
            $data = [];
            $data['userId'] = $reportUserId;

            $data['reason'] = explode(',', request()->type) ?? [];
        } else {
            $data = request()->all();
            $data['reason'] = explode(',', ($data['reason'] ?? ''));
        }
        $currentUser = request()->user();

        // Validate request
        $validator = Validator::make($data, [
            'userId' => 'required|integer|exists:users,id',
            'reason' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        foreach ($data['reason'] as $reason) {
            if (!in_array($reason, UserReportTypes::all()->pluck('type')->toArray())) {
                return response()->json([
                    'error' => 'wrong_type',
                ], 422);
            }

            $userReportedRepository = new UserReportedRepository();
            $userReportedRepository->reportUser($currentUser->id, $data['userId'], $reason);

            if ($reason == 'spam') {
                $reports = $userReportedRepository->gerReportsNumber($data['userId'], $reason);
                if ($reports >= env('SPAM_REPORTS_LIMIT_FOR_USER', 3)) {
                    /** @var User $user */
                    $user = User::find($data['userId']);
                    $user->suspend('reported');
                    (new ChatService())->setMessageIsSuspendedOrActiveForRecipients($user, User::STATUS_SUSPENDED);
                }
            }
        }

        return response()->json('ok');
    }

    /**
     * @param $playerId
     * @return string
     */
    public function assignOneSignalId($playerId, UserRepository $userRepository)
    {
        $me = \Auth::user();

        $player = new OnesignalPlayer(['player_id' => $playerId]);

        try {
            $me->onesignalPlayers()->save($player);
        } catch (\Exception $e) {
            //do nothing if player id is already assigned
        }

        //enable push notifications settings for the user
        if (is_null($me->push_notifications)) {
            $userRepository->updateObject($me, ['push_notifications' => 'yes']);
        }

        return $me->push_notifications;
    }
}
