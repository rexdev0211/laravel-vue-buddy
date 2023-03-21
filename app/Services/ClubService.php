<?php

namespace App\Services;

use App\Event;
use App\EventInvitation;
use App\EventMembership;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Redis;

class ClubService
{
    const
        DISTANCE_INITIAL = 300000,
        DISTANCE_MAX = 500000;

    /** @var User */
    protected $currentUser;

    /** @var array */
    protected $blockedUsersIds;

    /** @var string */
    protected $filterType;

    /** @var string */
    protected $minDate;

    /** @var string */
    protected $date;

    /** @var int|null */
    protected $page;

    /** @var int */
    protected $limit;

    /** @var array|null */
    protected $except;

    protected function handleParams(): void
    {
        // Get blocked users and users who blocked current user list
        $this->setBlockedUsersIds(
            $this->currentUser->getBlockedUsersIds()
        );

        // Event consider going "today" till 2 am
        // $minDate = Carbon::today();
        // if (Carbon::now()->hour < 2) {
        //     $minDate->subDay();
        // }
        // $this->setMinDate($minDate->format('Y-m-d'));
    }

    /**
     * @param int $datesCount
     * @return array
     */
    protected function handleDates(int $datesCount = 5): array
    {
        $dates = [];
        $start = !empty($this->date) ? Carbon::create($this->date) : Carbon::today();

        for ($i = 0; $i < $datesCount; $i++) {
            $dates[] = $start->format('Y-m-d');
            $start->addDay();
        }

        return $dates;
    }

    protected function getInvitedClubs($status)
    {
        $invitations = [];

        if ($this->filterType == 'my_clubs') {
            $clubInvitationsIds = EventInvitation::where('user_id', $this->currentUser->id)
                ->whereNotIn('invited_by_user_id', $this->blockedUsersIds)
                ->whereIn('status', [
                    $status,
                ])
                ->get()
                ->pluck('event_id');

            $invitations = Event::whereIn('id', $clubInvitationsIds)
                ->where('type', Event::TYPE_CLUB)
                ->get()
                ->map(function ($event) {
                    /** @var Event $event */
                    return $event->getAttributesByMode(Event::ATTRIBUTES_MODE_DISCOVER, $this->currentUser);
                })
                ->toArray();
        }

        return $invitations;
    }

    /**
     * @return array
     */
    public function getClubs(): array
    {
        $this->handleParams();

        if ($this->filterType == 'discover') {
            // Discover clubs
            $clubsNearBy = $this->getClubsByDistance(true, self::DISTANCE_INITIAL, $this->page * $this->limit, $this->limit);

            $clubsMore = [];
            if (count($clubsNearBy['clubs']) == 0) {
                $clubsMore = $this->getClubsByDistance(false, self::DISTANCE_INITIAL, $this->page * $this->limit  - $clubsNearBy['totalCount'], $this->limit);
            } else if (count($clubsNearBy['clubs']) < $this->limit) {
                $clubsMore = $this->getClubsByDistance(false, self::DISTANCE_INITIAL, 0, $this->limit - count($clubsNearBy['clubs']));
            }

            $clubsTotalCount = $this->getClubsTotalCount();

            $result = [
                'clubs_nearby' => $clubsNearBy['clubs'],
                'clubs_more' => $clubsMore['clubs'],
                'clubs_remained' => $clubsTotalCount - ($this->page + 1) * $this->limit,
            ];
        } else {
            // My Clubs
            $currentUser = $this->currentUser;

            $clubsInvited = $this->getInvitedClubs(EventInvitation::STATUS_PENDING);
            $clubsAdmin = $currentUser->clubs
                ->filter(function ($event) {
                    return $event->type == Event::TYPE_CLUB;
                })
                ->map(function ($event) use ($currentUser){
                    /** @var Event $event */
                    return $event->getAttributesByMode(Event::ATTRIBUTES_MODE_DISCOVER, $currentUser);
                });
            
            $clubMembersIds = EventMembership::where('user_id', $currentUser->id)
                ->whereIn('status', [EventMembership::STATUS_MEMBER, EventMembership::STATUS_REQUESTED])
                ->get()
                ->pluck('event_id');
            
            $clubsMember = Event::whereIn('id', $clubMembersIds)
                ->where('type', Event::TYPE_CLUB)
                ->get()
                ->map(function ($event) {
                    /** @var Event $event */
                    return $event->getAttributesByMode(Event::ATTRIBUTES_MODE_DISCOVER, $this->currentUser);
                })
                ->toArray();

            $result = [
                'clubs_invited' => $clubsInvited,
                'clubs_admin' => $clubsAdmin,
                'clubs_member' => $clubsMember,
            ];
        }

        return $result;
    }

    protected function getClubsByDistance(bool $nearBy = true, int $distance, int $offset, int $limit)
    {
        $currentUser = $this->currentUser;

        $clubsQuery = Event::selectRaw(
            "events.*, ST_Distance_sphere(ST_GeomFromText('point(".$this->currentUser->lat." ".$this->currentUser->lng.")', 4326), events.location_geom) AS distanceMeters"
        )
            ->with([
                'members',
                'photos',
                'videos',
                'user',
                'likes' => function($query) use ($currentUser) {
                    $query->with('userActive')
                        ->whereHas('userActive', function ($query) use ($currentUser) {
                            $query->where('users.status', User::STATUS_ACTIVE);

                            if ($currentUser->status == User::STATUS_GHOSTED) {
                                $query->orWhere('users.id', $currentUser->id);
                            }
                        });
                    }
                ])
            ->whereIn('events.status', [Event::STATUS_ACTIVE, Event::STATUS_APPROVED])
            ->where('events.type', Event::TYPE_CLUB)
            ->where('is_private', 0)
            ->whereHas('user', function ($query) use ($currentUser) {
                $query->where('id', '<>', $currentUser->id)->where('status', 'active')->whereNull('deleted_at');
            })
            ->having('distanceMeters', ($nearBy ? '<=' : '>'), $distance)
            ->orderByRaw('CASE WHEN TIMESTAMPDIFF(HOUR, events.created_at, NOW()) < 48 THEN created_at ELSE distanceMeters END ASC');

        if ($this->blockedUsersIds) {
            $clubsQuery = $clubsQuery->whereNotIn('user_id', $this->blockedUsersIds);
        }

        if (!empty($this->except)) {
            $clubsQuery = $clubsQuery->whereNotIn('events.id', $this->except);
        }

        $totalCount = $clubsQuery->get()->count();

        if (!empty($limit)) {
            $clubsQuery = $clubsQuery->skip($offset)->limit($limit);
        }

        $clubs = $clubsQuery->get()->map(function (Event $club) {
            return $club->getAttributesByMode(Event::ATTRIBUTES_MODE_DISCOVER, $this->currentUser);
        })->toArray();

        return [
            'clubs' => @array_values($clubs) ?? [],
            'totalCount' => $totalCount
        ];
    }

    protected function getClubsTotalCount(): int
    {
        $currentUser = $this->currentUser;

        $clubsQuery = Event::selectRaw(
            "events.*, ST_Distance_sphere(ST_GeomFromText('point(".$this->currentUser->lat." ".$this->currentUser->lng.")', 4326), events.location_geom) AS distanceMeters"
        )
            ->with([
                'members',
                'photos',
                'videos',
                'user',
                'likes' => function($query) use ($currentUser) {
                    $query->with('userActive')
                        ->whereHas('userActive', function ($query) use ($currentUser) {
                            $query->where('users.status', User::STATUS_ACTIVE);

                            if ($currentUser->status == User::STATUS_GHOSTED) {
                                $query->orWhere('users.id', $currentUser->id);
                            }
                        });
                    }
                ])
            ->whereIn('events.status', [Event::STATUS_ACTIVE, Event::STATUS_APPROVED])
            ->where('events.type', Event::TYPE_CLUB)
            ->where('is_private', 0)
            ->whereHas('user', function ($query) use ($currentUser) {
                $query->where('id', '<>', $currentUser->id)->where('status', 'active')->whereNull('deleted_at');
            });

        if ($this->blockedUsersIds) {
            $clubsQuery = $clubsQuery->whereNotIn('user_id', $this->blockedUsersIds);
        }

        if (!empty($this->except)) {
            $clubsQuery = $clubsQuery->whereNotIn('events.id', $this->except);
        }

        $totalCount = $clubsQuery->get()->count();

        return $totalCount;
    }

    protected function getClubsByDate(string $date, ?int $limit, int $distanceMin, int $distanceMax): array
    {
        $currentUser = $this->currentUser;

        $eventsQuery = Event::selectRaw(
            "events.*, ST_Distance_sphere(ST_GeomFromText('point(".$this->currentUser->lat." ".$this->currentUser->lng.")', 4326), events.location_geom) AS distanceMeters"
        )
            ->with([
                'members',
                'photos',
                'videos',
                'user',
                'likes' => function($query) use ($currentUser) {
                    $query->with('userActive')
                        ->whereHas('userActive', function ($query) use ($currentUser) {
                            $query->where('users.status', User::STATUS_ACTIVE);

                            if ($currentUser->status == User::STATUS_GHOSTED) {
                                $query->orWhere('users.id', $currentUser->id);
                            }
                        });
                    }
            ])
            ->whereIn('events.status', [Event::STATUS_ACTIVE, Event::STATUS_APPROVED])
            ->where('type', $this->filterType)
            ->where('event_date', $date)
            ->where('is_private', 0)
            ->whereHas('user', function ($query) {
                $query->where('status', 'active')->whereNull('deleted_at');
            })
            ->having('distanceMeters', '>=', $distanceMin)
            ->having('distanceMeters', '<=', $distanceMax)
            ->orderBy('distanceMeters', 'asc');

        if ($this->blockedUsersIds) {
            $eventsQuery = $eventsQuery->whereNotIn('user_id', $this->blockedUsersIds);
        }

        if (!empty($limit)) {
            $eventsQuery = $eventsQuery->skip($this->page * $limit)->limit($limit);
        }

        if (!empty($this->except)) {
            $eventsQuery = $eventsQuery->whereNotIn('events.id', $this->except);
        }

        $events = $eventsQuery->get()->map(function ($event) {
            return $event->getAttributesByMode(Event::ATTRIBUTES_MODE_DISCOVER, $this->currentUser);
        })->toArray();

        return @array_values($events) ?? [];
    }

    protected function getClubsByDateCount(string $date, ?int $limit, int $distanceMin, int $distanceMax): int
    {
        $eventsQuery = Event::whereIn('status', [Event::STATUS_ACTIVE, Event::STATUS_APPROVED])
            ->where('type', $this->filterType)
            ->where('event_date', $date)
            ->where('is_private', 0)
            ->whereHas('user', function ($query) {
                $query->where('status', 'active')->whereNull('deleted_at');
            })
            ->whereRaw("ST_Distance_sphere(
                ST_GeomFromText('point(".$this->currentUser->lat." ".$this->currentUser->lng.")', 4326), 
                location_geom
            ) BETWEEN ". $distanceMin . " AND " . $distanceMax);

        if ($this->blockedUsersIds) {
            $eventsQuery = $eventsQuery->whereNotIn('user_id', $this->blockedUsersIds);
        }

        if (!empty($limit)) {
            $eventsQuery = $eventsQuery
                            ->skip($this->page * $limit)
                            ->limit($limit);
        }

        if (!empty($this->except)) {
            $eventsQuery = $eventsQuery->whereNotIn('id', $this->except);
        }

        return $eventsQuery->count();
    }

    /**
     * @return User
     */
    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    /**
     * @param User $currentUser
     */
    public function setCurrentUser(User $currentUser): void
    {
        $this->currentUser = $currentUser;
    }

    /**
     * @return string
     */
    public function getFilterType(): string
    {
        return $this->filterType;
    }

    /**
     * @param string $filterType
     */
    public function setFilterType(string $filterType): void
    {
        $this->filterType = $filterType;
    }

    /**
     * @return string|null
     */
    public function getDate(): ?string
    {
        return $this->date;
    }

    /**
     * @param string $date|null
     */
    public function setDate(?string $date): void
    {
        $this->date = $date;
    }

    /**
     * @return array
     */
    public function getBlockedUsersIds(): array
    {
        return $this->blockedUsersIds;
    }

    /**
     * @param array $blockedUsersIds
     */
    public function setBlockedUsersIds(array $blockedUsersIds): void
    {
        $this->blockedUsersIds = $blockedUsersIds;
    }

    /**
     * @return string
     */
    protected function getMinDate(): string
    {
        return $this->minDate;
    }

    /**
     * @param string $minDate
     */
    protected function setMinDate(string $minDate): void
    {
        $this->minDate = $minDate;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return array|null
     */
    public function getExcept(): ?array
    {
        return $this->except;
    }

    /**
     * @param array|null $except
     */
    public function setExcept(?array $except): void
    {
        $this->except = $except;
    }
}
