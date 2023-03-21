<?php namespace App\Repositories;

use App\EventLike;

class EventNotificationRepository extends BaseRepository
{
    public function __construct(EventLike $model = null)
    {
        if (empty($model)){
            $model = new EventLike();
        }
        parent::__construct($model);
    }

    /**
     * @param int $userId
     * @param int $eventId
     *
     * @return EventLike
     */
    public function createEventLike(int $userId, int $eventId): EventLike
    {
        /** @var EventLike $eventNotification */
        $eventNotification = $this->model->create([
            'user_id' => $userId,
            'event_id' => $eventId
        ]);

        return $eventNotification;
    }

    /**
     * @param int $userId
     * @param int $eventId
     *
     * @return bool|null
     */
    public function removeEventLike(int $userId, int $eventId): ?bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->delete();
    }

    /**
     * @param int $userId
     * @param int $eventId
     *
     * @return bool
     */
    public function userLikedEvent(int $userId, int $eventId): bool
    {
        return $this
            ->model
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->exists();
    }

}