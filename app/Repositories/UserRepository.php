<?php

namespace App\Repositories;

use App\Enum\ProTypes;
use App\User;
use App\UserBlocked;
use App\UserSharingUrl;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;

class UserRepository extends BaseRepository
{
    public function __construct(User $model = null)
    {
        if (empty($model)) {
            $model = new User();
        }
        parent::__construct($model);
    }

    /**
     * @param $perPage
     * @param $orderBy
     * @param $orderBySort
     * @param bool $except
     * @param bool $onlyOnline
     * @return mixed
     */
    public function getAllUsersPaginated(
        $page,
        $perPage,
        $orderBy,
        $orderBySort,
        $filterEmail = false,
        $filterName = false,
        $filterBuddyLink = false,
        $filterId = false,
        $filterTrashed = false,
        $filterActivity = false,
        $filterCountry = false,
        $filterState = false,
        $filterLocality = false,
        $filterLanguage = false,
        $filterRegistration = false,
        $filterGroup = false
    )
    {
        return $this
            ->orderBy($orderBy, $orderBySort)
            ->filterByEmailOrEmailOrig($filterEmail)
            ->filterByName($filterName)
            ->filterByBuddyLink($filterBuddyLink)
            ->filterBy('id', $filterId)
            ->filterBy('country_code', $filterCountry)
            ->filterBy('state', $filterState)
            ->filterBy('locality', $filterLocality)
            ->filterBy('language', $filterLanguage)
            ->filterBy('registered_via', $filterRegistration)
            ->filterByGroup($filterGroup)
            ->filterByTrashed($filterTrashed)
            ->filterByActivity($filterActivity)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @param $filterTrashed
     * @return $this
     */
    public function filterByTrashed($filterTrashed)
    {
        if (!$filterTrashed) {
            return $this;
        }

        //only trashed accounts
        if ($filterTrashed == 'only_deleted') {
            $this->whereNotNull('deleted_at');
        } //except deleted accounts
        elseif ($filterTrashed == 'except_deleted') {
            $this->whereNull('deleted_at');
        } //only suspended accounts
        elseif ($filterTrashed == 'only_suspended') {
            $this->where('status', 'suspended');
        } //except suspended accounts
        elseif ($filterTrashed == 'except_suspended') {
            $this->where('status', '!=', 'suspended');
        } //only ghosted accounts
        elseif ($filterTrashed == 'only_ghosted') {
            $this->where('status', 'ghosted');
        } //except suspended accounts
        elseif ($filterTrashed == 'except_ghosted') {
            $this->where('status', '!=', 'ghosted');
        } //only deleted and suspended accounts
        elseif ($filterTrashed == 'only_deleted_suspended') {
            $this->where(function ($query) {
                $query->whereIn('status', ['suspended', 'ghosted'])
                    ->orWhereNotNull('deleted_at');
            });
        } //except suspended accounts
        elseif ($filterTrashed == 'except_deleted_suspended') {
            $this->where('status', 'active')
                ->whereNull('deleted_at');
        } //all accounts (including trashed)
        else {
//            $this->withTrashed();
        }

        return $this;
    }

    /**
     * @param $filterActivity
     * @return $this
     */
    public function filterByActivity($filterActivity)
    {
        if (!$filterActivity) {
            return $this;
        }

        if ($filterActivity == 'deactivated') {
            $this->where('status', User::STATUS_DEACTIVATED);
        } elseif ($filterActivity == 'dormant') {
            $yearAgo = Carbon::now()->subYear()->toDateTimeString();
            $this
                ->whereNotNull('last_active')
                ->where('last_active', '<', $yearAgo);
        }

        return $this;
    }

    /**
     * @param $id
     * @return bool
     */
    public function softDeleteUserById($id)
    {
        $user = $this->findUser($id);
        return $user->softDeleteUser();
    }

    /**
     * @param $id
     */
    public function softRestoreUserById($id)
    {
        $user = $this->findWithTrashedUser($id);
        $user->softUndeleteUser();
    }

    /**
     * @param User $retriever
     *
     * @return array
     */
    public function getOnlineFavorites(User $retriever): array
    {
        $this->selectRaw("*, ST_Distance_sphere(ST_GeomFromText('point(".$retriever->lat." ".$retriever->lng.")', 4326), users.location) AS distanceMeters")
             ->where(function ($query) {
                 $query->where(function ($query) {
                     $query->where('pro_expires_at', '>', now()->format('Y-m-d H:i:s'))
                           ->where(function ($query) {
                               $query->where(function ($query) {
                                   $query->where('discreet_mode', '=', 1)
                                         ->where('invisible', '=', 0);
                               })->orWhere(function ($query) {
                                   $query->where('discreet_mode', '=', 0);
                               });
                           });
                 })->orWhere(function ($query) {
                     $query->where('pro_expires_at', '<=', now()->format('Y-m-d H:i:s'))
                           ->orWhereNull('pro_expires_at');
                 });
             })
             ->join('user_favorites_map', function ($q) use ($retriever) {
                 $q->whereRaw('user_favorites_map.user_favorite_id = users.id')
                   ->where('user_favorites_map.user_id', '=', $retriever->id);
             })
             ->where('status', '=', 'active')
             ->whereNull('deleted_at')
             ->orderBy('distanceMeters', 'asc')
             ->excludeBlockedUsers();

        $onlineFavorites = $this->get();

        $onlineFavorites = $onlineFavorites
            ->filter(function (User $user) {
                return $user->wasRecentlyOnline();
            })
            ->map(function (User $user) use ($retriever) {
                return $user->getAttributesByMode(User::ATTRIBUTES_MODE_GENERAL, $retriever);
            })
            ->values()
            ->toArray();

        return $onlineFavorites;
    }

    /**
     * @return mixed
     */
    public function getUsersWithoutMessagesInLastMonth()
    {
        $from = Carbon::today()->subMonth();

        $to = Carbon::today();

        return $this
            ->select(['id', 'name', 'email', 'language'])
            ->whereNotExists(function ($query) use ($from, $to) {
                $query->select(\DB::raw(1))
                    ->from('messages')
                    ->whereRaw('messages.user_to = users.id')
                    ->whereBetween('idate', [$from, $to]);
            })
            ->where('login_reminder_sent', '<=', $from)
            ->where('last_active', '<=', $from)
            ->where('email_reminders', '<>', 'never')
            ->whereNull('deleted_at')
            ->where(function ($query) {
                $query->where('email_validation', '<>', 'bounce')
                    ->orWhereNull('email_validation');
            })
            ->where('status', 'active')
            ->get();
    }

    /**
     * @param $userIds
     */
    public function updateMonthlyReminder($userIds)
    {
        $this->whereIn('id', $userIds)
            ->updateAll(['login_reminder_sent' => \DB::raw('CURDATE()')]);
    }

    /**
     * @param $userId
     * @param User $user
     * @return object
     */
    public function generateDeletedUser($userId, $user)
    {
        $name = 'Deleted account';

        if (is_null($user)) {
            $delUser = \DB::table('users_deleted')->where('id', $userId)->first();

            if (!is_null($delUser)) {
                $name = $delUser->name;
            }
        } elseif ($user->isDeleted()) {
            $name = $user->name;
        }

        $user = [
            'id' => $userId,
            'name' => $name,
            'deleted_at' => 1,
            'photo' => null,
            'distanceMeters' => 0
        ];

        return (object)$user;
    }

    /**
     * @param User $user
     * @param array $updateData
     * @return User
     */
    public function updateUserStatus(User $user, $updateData = [])
    {
        $refreshSeconds = intval(config('const.REFRESH_LAST_ACTIVE_SECONDS') + 30);
        $updateData['time_spent_online'] = \DB::raw("
            time_spent_online + IF(TIMESTAMPDIFF(SECOND, last_active, NOW()) < $refreshSeconds, TIMESTAMPDIFF(SECOND, last_active, NOW()), 0)
        ");

//        !!! it's important last_active field to be specified the last so the IF() condition would calculate correct
//        $updateData['last_active'] = \DB::raw('NOW()');

        /* Update last active ONLY for non-descreet/hidden mode */
        if ($user->isPro()) {
            if (!$user->discreet_mode) {
                $updateData['last_active'] = now();
            }
        } else {
            $updateData['last_active'] = now();
        }

        $updateData['ip'] = \Helper::getUserIpFromRequest();

//        !!! if we use updateObject instead of \DB::update -> laravel reorders fields and puts last_active field before time_spent_online,
//        (its' the order from database) and IF() condition equals 0 always because NOW() and last_active are always equal
//        return $this->updateObject($user, $updateData);
        \DB::table('users')
            ->where('id', $user->id)
            ->update($updateData);

//        !!! so we need in the end an extra select to get correct information from database
//        return $user->find($user->id);

        foreach ($updateData as $key => $value) {
            //we don't need this info updated in user, it will be {} object, not string
            if (!in_array($key, ['time_spent_online', 'location'])) {
                $user[$key] = $value;
            }
        }

        return $user;
    }

    /**
     * Add condition to exclude blocked users from results
     */
    private function excludeBlockedUsers()
    {
        $this->whereNotExists(function ($query) {
            $query->select(\DB::raw(1))
                ->from('user_blocked_map')
                ->whereRaw('user_id = ? and user_blocked_id = users.id', [\Auth::id()]);
        });

        $this->whereNotExists(function ($query) {
            $query->select(\DB::raw(1))
                ->from('user_blocked_map')
                ->whereRaw('user_blocked_id = ? and user_id = users.id', [\Auth::id()]);
        });
    }

    /**
     * @param array $ids
     * @param float $longitude
     * @param float $latitude
     *
     * @return Collection
     */
    public function getUsersStatuses(array $ids, float $latitude, float $longitude): Collection
    {
        return $this
            ->selectRaw("*, St_distance_sphere(ST_GeomFromText('Point(".$latitude." ".$longitude.")', 4326), location) AS distanceMeters")
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * @return User[]
     */
    public function getAllUsers($filterTrashed, $filterCountry, $filterState, $filterLocality, $filterLanguage)
    {
        return $this
            ->filterByTrashed($filterTrashed)
            ->filterBy('country_code', $filterCountry)
            ->filterBy('state', $filterState)
            ->filterBy('locality', $filterLocality)
            ->filterBy('language', $filterLanguage)
            ->get();
    }

    /**
     * @param $id
     *
     * @return null|User
     */
    public function findUser($id): ?User
    {
        return $this
            ->where('id', $id)
            ->orWhere('link', $id)
            ->first();
    }

    /**
     * @param $id
     * @return User
     */
    public function findWithTrashedUser($id)
    {
        return $this->findWithTrashed($id);
    }

    /**
     * @param $filterName
     * @return $this
     */
    public function filterByName($filterName)
    {
        if (!$filterName) {
            return $this;
        }

        return $this->where('name', 'like', '%' . $filterName . '%');
    }

    /**
     * @param $filterBuddyLink
     * @return $this
     */
    public function filterByBuddyLink($filterBuddyLink)
    {
        if (!$filterBuddyLink) {
            return $this;
        }

        return $this->where('link', 'like', '%' . $filterBuddyLink . '%');
    }

    /**
     * @param $filterEmail
     * @return $this
     */
    public function filterByEmailOrEmailOrig($filterEmail)
    {
        if (!$filterEmail) {
            return $this;
        }

        return $this->where(function ($q) use ($filterEmail) {
            $q->where('email', 'like', '%' . $filterEmail . '%')
                ->orWhere('email_orig', 'like', '%' . $filterEmail . '%');
        });
    }

    /**
     * @param $filterGroup
     * @return $this
     */
    public function filterByGroup($filterGroup)
    {
        $now = DB::raw("NOW()");
        switch ($filterGroup) {
            case 'free':
                return $this->where('user_group', '=', User::GROUP_MEMBER)
                    ->where(function ($query) use ($now) {
                        $query->whereNull('pro_expires_at')
                            ->orWhere('pro_expires_at', '<=', $now);
                    });
                break;
            case 'pro_all':
                return $this->where('user_group', '=', User::GROUP_MEMBER)
                    ->where('pro_expires_at', '>', $now);
                break;
            case 'pro_paid':
                return $this->where('user_group', '=', User::GROUP_MEMBER)
                    ->where('pro_expires_at', '>', $now)
                    ->where('pro_type', '=', ProTypes::PAID);
                break;
            case 'pro_manual':
                return $this->where('user_group', '=', User::GROUP_MEMBER)
                    ->where('pro_expires_at', '>', $now)
                    ->where('pro_type', '=', ProTypes::MANUAL);
                break;
            case 'pro_coupon':
                return $this->where('user_group', '=', User::GROUP_MEMBER)
                    ->where('pro_expires_at', '>', $now)
                    ->where('pro_type', '=', ProTypes::COUPON);
                break;
            case 'staff':
                return $this->where('user_group', '=', User::GROUP_STAFF);
                break;
        }

        return $this;
    }

    /**
     * @param $email
     * @return mixed
     */
    public function findByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createUser(array $data)
    {
        $data ['password'] = \Hash::make($data['password']);
        unset($data['password2']);

        /** @var User $model */
        $model = $this->model->create($data);
        $model->assignBuddyLink();

        return $model;
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function updateUser($id, array $data)
    {
        if (isset($data['password']) && $data['password']) {
            $data['password'] = \Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if (isset($data['show_age'])) {
            $data['show_age'] = $data['show_age'] == 'yes'
                ? 'yes'
                : 'no';
        }

        unset($data['password2']);
        return $this->update($id, $data);
    }

    /**
     * @param $userId
     * @param array $tagsIds
     */
    public function updateTags($userId, array $tagsIds)
    {
        $user = $this->findUser($userId);

        $user->tags()->sync(array_values($tagsIds));
    }

    /**
     * @return array
     */
    public function getProbablySpammers()
    {
        $msgLimit = 50;
        $exceptUserId = config('const.BB_USER_ID');

        $query = "SELECT distinct user_from FROM `messages`
                    WHERE idate > NOW() - INTERVAL 1 WEEK
                    GROUP BY DATE_FORMAT(idate, '%Y-%m-%d %H'), user_from
                    HAVING count(DISTINCT user_to) > :count and user_from <> :except";

        $rows = \DB::select($query, ['count' => $msgLimit, 'except' => $exceptUserId]);

        if (count($rows)) {
            $ids = array_pluck($rows, 'user_from');

            $users = $this->where('status', 'active')
                ->whereNull('deleted_at')
                ->whereIn('id', $ids)
                ->get();

            return $users;
        }

        return collect([]);
    }

    /**
     * @param $id
     * @param $page
     * @param $limit
     * @return array
     */
    public function getBlockedUsers($id, $page, $limit): array
    {
        $blockedUsers = UserBlocked::with('blocked')
                                    ->where('user_id', $id)
                                    ->forPage($page + 1, $limit)
                                    ->get();

        $users = $blockedUsers->map(function ($user) {
            /** @var User $blockedRelation */
            $blockedRelation = $user->blocked;

            return $blockedRelation->getAttributesByMode(User::ATTRIBUTES_MODE_GENERAL, $blockedRelation);
        })
        ->toArray();

        return $users;
    }

    /**
     * @param $userId
     * @param $urlId
     * @return mixed
     */
    public function createUserSharingUrl($userId, $urlId)
    {
        return UserSharingUrl::insert([
            'user_id' => $userId,
            'url_id' => $urlId
        ]);
    }
}
