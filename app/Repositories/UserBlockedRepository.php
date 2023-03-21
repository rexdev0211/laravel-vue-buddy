<?php namespace App\Repositories;

use App\Message;
use App\UserBlocked;

class UserBlockedRepository extends BaseRepository
{
    public function __construct(UserBlocked $model = null)
    {
        if (empty($model)){
            $model = new UserBlocked();
        }
        parent::__construct($model);
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getBlockedUsersCount($userId) {
        return $this->where('user_id', $userId)->count();
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function resetBlockedUsers($userId) {

        $blockedUsersIds = $this->where('user_id', $userId)
                                ->get()
                                ->pluck('user_blocked_id')
                                ->toArray();

        Message::where(function ($query) use ($userId, $blockedUsersIds) {
                $query->where('user_from', $userId)
                      ->whereIn('user_to', $blockedUsersIds)
                      ->update([
                          'is_blocked_by_sender' => 0,
                      ]);
            })
            ->orWhere(function ($query) use ($userId, $blockedUsersIds) {
                $query->whereIn('user_from', $blockedUsersIds)
                      ->where('user_to', $userId)
                      ->update([
                          'is_blocked_by_recipient' => 0,
                      ]);
            });

        $this->where('user_id', $userId)->delete();

        return $blockedUsersIds;
    }

    /**
     * @param $userId
     * @param $blockedUserId
     * @return mixed
     */
    public function resetBlockedUser($userId, $blockedUserId)
    {
        Message::where(function ($query) use ($userId, $blockedUserId) {
            $query->where('user_from', $userId)
                ->where('user_to', $blockedUserId)
                ->update([
                    'is_blocked_by_sender' => 0,
                ]);
            })
            ->orWhere(function ($query) use ($userId, $blockedUserId) {
                $query->where('user_from', $blockedUserId)
                    ->where('user_to', $userId)
                    ->update([
                        'is_blocked_by_recipient' => 0,
                    ]);
            });

        return $this->where('user_id', $userId)
                    ->where('user_blocked_id', $blockedUserId)
                    ->delete();
    }

}