<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Services\MobileNotificationsService;
use Illuminate\Http\JsonResponse;

use App\Events\NewNotificationReceived;
use App\Events\NewVisitorReceived;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserVisitRepository;
use App\Repositories\EventNotificationRepository;
use App\Repositories\EventRepository;
use App\User;

class NotificationsController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function getVisitors(): JsonResponse
    {
        $lastVisitId = request()->get('lastVisitId', 0);
        $selectCount = config('const.LOAD_VISITORS_LIMIT');
        $userVisitors = (new UserVisitRepository())->getUserVisitors(auth()->user(), $selectCount, $lastVisitId, 0);

        return response()->json($userVisitors);
    }

    /**
     * @return JsonResponse
     */
    public function getVisitedUsers(): JsonResponse
    {
        $lastVisitId = request()->get('lastVisitId', 0);
        $selectCount = config('const.LOAD_VISITORS_LIMIT');
        $visitedUsers = (new UserVisitRepository())->getVisitedUsers(auth()->user(), $selectCount, $lastVisitId, 0);

        return response()->json($visitedUsers);
    }

    /**
     * @return JsonResponse
     */
    public function getNotifications(): JsonResponse
    {
        $maxNotificationId = request()->get('lastNotificationId', 0);
        $selectCount = config('const.LOAD_TAPS_LIMIT');
        $userNotifications = (new NotificationRepository())->getUserNotifications(auth()->user(), $selectCount, $maxNotificationId, 0);

        return response()->json($userNotifications);
    }

    /**
     * @return JsonResponse
     */
    public function clearNotifications(): JsonResponse
    {
        $notificationRepository = new NotificationRepository();
        $userRepository = new UserRepository();

        $me = request()->user();
        $notificationRepository->clearUserNotifications($me->id);
        $userRepository->updateUser($me->id, ['has_new_messages' => false]);

        return response()->json('ok');
    }

    /**
     * @return JsonResponse
     */
    public function addWave(): JsonResponse
    {
        $recipientId = request()->get('recipientId');
        $type = request()->get('type');

        $notificationRepository = new NotificationRepository();
        $userRepository = new UserRepository();

        $currentUser = request()->user();

        if (!in_array($type, ['hand', 'pig', 'donut', 'nicebody', 'fire', 'sweat', 'devil', 'video', 'rocket', 'beer', 'ring', 'now', 'apple', 'love', 'banana'])) {
            return response()->json("Can't understand type of wave", 422);
        }

        if (in_array($type, ['video', 'rocket', 'beer', 'ring', 'donut', 'nicebody']) && !$currentUser->isPro()) {
            return response()->json("This type of wave is only for PRO", 422);
        }

        if ($notificationRepository->waveIsAllowed($currentUser->id, $recipientId)) {
            $recipient = User::whereId($recipientId)->first();
            $notification = $notificationRepository->createUserNotification($currentUser->id, $recipientId, $type);
            $notification->user_from = $currentUser->getAttributesByMode(User::ATTRIBUTES_MODE_GENERAL, $recipient);

            if (!$currentUser->isGhosted() || $recipient->isGhosted()) {
                event(new NewNotificationReceived($notification->toArray()));
                $userRepository->updateUser($notification['user_to'], [
                    'has_notifications' => true,
                    'has_new_notifications' => true
                ]);
                (new MobileNotificationsService())->newNotification($notification, [$recipient]);
            }
        }

        $response = !empty($notification) ?
            $notification->toArray()
            :
            [];

        return response()->json($response);
    }

    /**
     * @return JsonResponse
     */
    public function addVisit(): JsonResponse
    {
        $userId = request()->get('userId');

        $userRepository = new UserRepository();
        $userVisitRepository = new UserVisitRepository();

        /** @var User $loggedUser */
        $loggedUser = request()->user();
        $viewedUser = $userRepository->findUser($userId);
        if ($viewedUser->isBlockedBy($loggedUser)) {
            return response()->json(['blocked_user' => "Can't view profile of blocked user"]);
        }

        $loggedUser->incrementViewedOtherProfiles();
        $viewedUser->incrementHisProfileViewed();
        $loggedUserInvisible = $loggedUser->discreet_mode && $loggedUser->isPro();
        $userVisitRepository->createUserVisit($loggedUser->id, $viewedUser->id, $loggedUserInvisible);

        $dataForVisited = [
            'visitors' => $userVisitRepository->getUserVisitors($viewedUser, config('const.LOAD_VISITORS_LIMIT')),
            'visited_id' => $viewedUser->id,
        ];

        if ((!$loggedUser->isGhosted() || $viewedUser->isGhosted()) && !$loggedUserInvisible) {
            event(new NewVisitorReceived($dataForVisited));
            $userRepository->updateUser($viewedUser->id, [
                'has_notifications' => true,
                'has_new_visitors' => true
            ]);
        }

        return response()->json([
            'visited' => $userVisitRepository->getVisitedUsers($loggedUser, config('const.LOAD_VISITORS_LIMIT')),
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function likeEvent(): JsonResponse
    {
        $eventId = request()->get('eventId');

        $eventRepository = new EventRepository();
        $eventLikeRepository = new EventNotificationRepository();
        $notificationRepository = new NotificationRepository();

        $currentUser = request()->user();
        $event = $eventRepository->findEvent($eventId);
        $exists = $eventLikeRepository->userLikedEvent($currentUser->id, $event->id);

        if (!$exists) {
            \DB::transaction(function () use ($event, $eventLikeRepository, $notificationRepository, $currentUser, $eventRepository) {
                $eventRepository->update($event->id, ['likes' => $event->likes + 1]);
                $eventLikeRepository->createEventLike($currentUser->id, $event->id);

                //if it's not my event - send a notification
                if (!$currentUser->isGhosted() && $event->user_id != $currentUser->id) {
                    $notification = $notificationRepository
                        ->createEventNotification('like', $currentUser->id, $event->user_id, $event->id)
                        ->load(['notificationEvent', 'userFrom']);

                    $notificationArray = $notification->toArray();
                    $notificationArray['user_from'] = $currentUser->getAttributesByMode(User::ATTRIBUTES_MODE_GENERAL, $event->user);

                    event(new NewNotificationReceived($notificationArray));
                    (new UserRepository())->updateUser($notificationArray['user_to'], [
                        'has_notifications' => true,
                        'has_new_notifications' => true
                    ]);

                    (new MobileNotificationsService())->newNotification($notification, [$event->user]);
                }
            });
            return response()->json('ok', 200);
        }

        return response()->json('User already liked the event', 422);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function dislikeEvent(): JsonResponse
    {
        $eventId = request()->get('eventId');

        $eventRepository = new EventRepository();
        $eventLikeRepository = new EventNotificationRepository();
        $notificationRepository = new NotificationRepository();

        $currentUser = request()->user();
        $event = $eventRepository->findEvent($eventId);
        $exists = $eventLikeRepository->userLikedEvent($currentUser->id, $event->id);

        if ($exists) {
            \DB::transaction(function () use ($event, $eventLikeRepository, $notificationRepository, $currentUser, $eventRepository) {
                $newLikes = $event->likes - 1;
                $eventRepository->update($event->id, ['likes' => $newLikes ? $newLikes : 0]);
                $eventLikeRepository->removeEventLike($currentUser->id, $event->id);
                $notificationRepository->removeEventNotification('like', $currentUser->id, $event->user_id, $event->id);
            });

            return response()->json('ok', 200);
        }

        return response()->json("User hasn't liked the event", 422);
    }
}
