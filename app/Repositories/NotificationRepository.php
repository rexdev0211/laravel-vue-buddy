<?php namespace App\Repositories;

use App\Event;
use App\Notification;
use App\User;
use Carbon\Carbon;
use Log;

class NotificationRepository extends BaseRepository
{
    /**
     * NotificationRepository constructor.
     *
     * @param Notification|null $model
     */
    public function __construct(Notification $model = null)
    {
        if (empty($model)) {
            $model = new Notification();
        }
        parent::__construct($model);
    }

    /**
     * @param int $senderId
     * @param int $recipientId
     * @param string $subType
     *
     * @return Notification
     */
    public function createUserNotification(int $senderId, int $recipientId, string $subType): Notification
    {
        $data = [
            'type' => 'wave',
            'sub_type' => $subType,
            'user_from' => $senderId,
            'user_to' => $recipientId,
            'is_read' => 'no'
        ];

        /** @var Notification $notification */
        $notification = $this->create($data);

        return $notification;
    }

    /**
     * @param $subType
     * @param $userFromId
     * @param $userToId
     * @param $eventId
     *
     * @return Notification
     */
    public function createEventNotification($subType, $userFromId, $userToId, $eventId): Notification
    {
        $data = [
            'type' => 'event',
            'sub_type' => $subType,
            'user_from' => $userFromId,
            'user_to' => $userToId,
            'event_id' => $eventId,
            'is_read' => 'no'
        ];

        /** @var Notification $notification */
        $notification = $this->create($data);

        return $notification;
    }

    /**
     * @param $subType
     * @param $userFromId
     * @param $userToId
     * @param $eventId
     *
     * @return bool|null
     */
    public function removeEventNotification($subType, $userFromId, $userToId, $eventId): ?bool
    {
        return $this->model
            ->where('type', 'event')
            ->where('sub_type', $subType)
            ->where('user_from', $userFromId)
            ->where('user_to', $userToId)
            ->where('event_id', $eventId)
            ->delete();
    }

    /**
     * @param $senderId
     * @param $recipientId
     *
     * @return bool
     */
    public function waveIsAllowed($senderId, $recipientId): bool
    {
        $last = $this
            ->where('user_from', $senderId)
            ->where('user_to', $recipientId)
            ->where('type', 'wave')
            ->orderBy('id', 'desc')
            ->first();

        if (!is_null($last) && Carbon::now()->subHours(24) < $last->idate) {
            return false;
        }

        return true;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getLastDayUserWaves(User $user): array
    {
        return $this->where('user_from', $user->id)
            ->where('type', 'wave')
            ->whereRaw('idate > NOW() - INTERVAL 24 HOUR')
            ->get()
            ->toArray();
    }

    /**
     * @param User $user
     * @param int $perPage
     * @param int $maxNotificationId
     * @param int $minNotificationId
     *
     * @return array
     */
    public function getUserNotifications(User $user, int $perPage = 25, int $maxNotificationId = 0, int $minNotificationId = 0): array
    {
        $baseQuery = $this->model
            ->with('notificationEvent')
            ->where('user_to', $user->id)
            ->join('users', function ($join) use ($user) {
                $join->on('users.id', '=', 'notifications.user_from')
                    ->whereIn('users.status', $user->isGhosted() ? ['active', 'ghosted'] : ['active'])
                    ->whereNull('deleted_at');
            })
            ->whereNotExists(function ($query) use ($user) {
                $query->select(\DB::raw(1))
                    ->from('user_blocked_map')
                    ->whereRaw('user_id = ? and user_blocked_id = users.id', [$user->id]);
            })
            ->whereNotExists(function ($query) use ($user) {
                $query->select(\DB::raw(1))
                    ->from('user_blocked_map')
                    ->whereRaw('user_blocked_id = ? and user_id = users.id', [$user->id]);
            })
            ->select('notifications.*');

        if ($maxNotificationId) {
            $baseQuery = $baseQuery->where('notifications.id', '<', $maxNotificationId);
        }

        if ($minNotificationId) {
            $baseQuery = $baseQuery->where('notifications.id', '>', $minNotificationId);
        }

        $notifications = $baseQuery
            ->orderBy('notifications.id', 'desc')
            ->limit($perPage)
            ->get();

		$notificationsProduced = $notifications->map(function ($notification) use ($user) {
			/** @var User $userFrom */
            $userFrom = $notification->userFrom;
		    $notification = $notification->toArray();
			$notification['user_from'] = $userFrom->getAttributesByMode(User::ATTRIBUTES_MODE_GENERAL, $user);
			return $notification;
		})
		->toArray();

		return $notificationsProduced;
    }

    /**
     * @param $forUserId
     *
     * @return void
     */
    public function clearUserNotifications($forUserId): void
    {
        $this
            ->where('user_to', $forUserId)
            ->updateAll(['is_read' => 'yes']);
    }

    /**
     * @param $maxDate
     *
     * @return bool|null
     */
    public function deleteOldNotifications($maxDate): ?bool
    {
        return $this->where('idate', '<', $maxDate)->delete();
    }
}
