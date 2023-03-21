<?php

namespace App\Services;

use App\Event;
use App\EventInvitation;
use App\EventMembership;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Redis;

class EventService
{
    const
        DISTANCE_INITIAL = 100000,
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
        $minDate = Carbon::today();
        if (Carbon::now()->hour < 2) {
            $minDate->subDay();
        }
        $this->setMinDate($minDate->format('Y-m-d'));
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

    protected function getInvitedEvents($status)
    {
        $invitations = [];

        if ($this->filterType == Event::TYPE_BANG) {
            $eventInvitationsIds = EventInvitation::where('user_id', $this->currentUser->id)
                ->whereNotIn('invited_by_user_id', $this->blockedUsersIds)
                ->whereIn('status', [
                    $status,
                ])
                ->get()
                ->pluck('event_id');

            $invitations = Event::whereIn('id', $eventInvitationsIds)
                ->where('event_date', '>=', now()->toDateString())
                ->get()
                ->filter(function(Event $event) {
                    if (Carbon::parse($event->event_date.' 23:59:59')->lessThan(now())) {
                        return false;
                    }

                    return true;
                })
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
    public function getEvents(): array
    {
        $this->handleParams();
        $result = [];
        $pendingInvitations = $this->getInvitedEvents(EventInvitation::STATUS_PENDING);
        $acceptedInvitations = $this->getInvitedEvents(EventInvitation::STATUS_ACCEPTED);

        if (!empty($pendingInvitations)) {
            $result[] = [
                'date' => 'invited',
                'events_range_low' => $pendingInvitations,
                'events_range_high' => [],
                'events_range_high_count' => 0,
            ];
        }

        if (!empty($acceptedInvitations)) {
            $result[] = [
                'date' => 'accepted invitations',
                'events_range_low' => $acceptedInvitations,
                'events_range_high' => [],
                'events_range_high_count' => 0,
            ];
        }

        if (empty($this->date)) {
            $dates = $this->getEventDates();

            foreach ($dates as $date) {
                $events    = $this->getEventsByDate($date, null, 0, self::DISTANCE_INITIAL);
                $returnMax = $this->getEventsByDateCount($date, null, self::DISTANCE_INITIAL, self::DISTANCE_MAX);

                $eventIds = collect($events)->pluck('id')->toArray();
                $this->setExcept(array_merge($this->except, $eventIds));

                if ($returnMax > 0 || count($events) > 0) {
                    $result[] = [
                        'date' => $date,
                        'events_range_low' => $events,
                        'events_range_high' => [],
                        'events_range_high_count' => $returnMax,
                    ];
                }
            }
        } else {
            // Get events
            $events = $this->getEventsByDate($this->date, $this->limit, self::DISTANCE_INITIAL, self::DISTANCE_MAX);

            // Exclude current event ids to capture if there are more events
            $eventIds = collect($events)->pluck('id')->toArray();
            $this->setExcept(array_merge($this->except, $eventIds));

            $distanceMin    = !empty($events) ? last($events)['distanceMeters'] : self::DISTANCE_INITIAL;
            $eventsMaxCount = $this->getEventsByDateCount($this->date, $this->limit, $distanceMin, self::DISTANCE_MAX);

            $result = [
                [
                    'date' => $this->date,
                    'events_range_low' => [],
                    'events_range_high' => $events,
                    'events_range_high_count' => $eventsMaxCount,
                ]
            ];
        }

        if (!count($result) && \Helper::isApp()) {
            $result[] = [
                'date'                    => date('Y-m-d'),
                'events_range_low'        => [],
                'events_range_high'       => [],
                'events_range_high_count' => 0,
            ];
        }

        return $result;
    }

    /**
     * @param string $date
     * @param int $distanceMin
     * @param int $distanceMax
     * @return int
     *
     * TODO: this method should be removed, due huge difference between getEvents and getEventsCountByDistance results...
     */
    protected function getEventsCountByDistance(string $date, int $distanceMin, int $distanceMax): int
    {
//        if (!empty($this->blockedUsersIds)) {
//            $blockedUsersClause = "users.id NOT IN(" . implode(',', $this->blockedUsersIds) . ") AND ";
//        }
//
//        if (!empty($this->except)) {
//            $exceptEvents = "events.id NOT IN(" . implode(',', $this->except) . ") AND ";
//        }
//
//        $result = DB::selectOne(
//            DB::raw("
//                SELECT
//                    count(*) as count
//                FROM (
//                    SELECT
//                        events.event_date,
//                        ST_Distance_sphere(point({$this->currentUser->lng}, {$this->currentUser->lat}), events.gps_geom) AS distanceMeters
//                    FROM
//                        events
//                    LEFT JOIN
//                        users ON events.user_id = users.id
//                    WHERE
//                        " . ($blockedUsersClause ?? '') . "
//                        " . ($exceptEvents ?? '') . "
//                        (users.status = 'active' AND users.deleted_at IS NULL)
//                        AND
//                        events.event_date = '{$date}'
//                        AND
//                        events.type = '{$this->filterType}'
//                    HAVING
//                        distanceMeters >= {$distanceMin}
//                        AND
//                        distanceMeters <= {$distanceMax}
//                ) as events_list
//            ")
//        );
//
//        return $result->count ?? 0;
    }

    protected function getEventDates(): array
    {
        if (!empty($this->blockedUsersIds)) {
            $blockedUsersClause = "users.id NOT IN(" . implode(',', $this->blockedUsersIds) . ") AND ";
        }

        $offset = $this->page * $this->limit;
        $distanceMax = self::DISTANCE_MAX;

        // Get event dates
        $dates = DB::select(
            DB::raw("
                SELECT
                    event_date
                FROM (
                    SELECT
                        events.event_date,
                        ST_Distance_sphere(ST_GeomFromText('point({$this->currentUser->lat} {$this->currentUser->lng})', 4326), events.location_geom) AS distanceMeters
                    FROM
                        events
                    LEFT JOIN
                        users ON events.user_id = users.id
                    WHERE
                        " . ($blockedUsersClause ?? '') . "
                        (users.status = 'active' AND users.deleted_at IS NULL)
                        AND
                        events.event_date >= '{$this->minDate}'
                        AND
                        events.is_sticky = 0
                        AND
                        events.type = '{$this->filterType}'
                        AND
                        events.status IN ('active', 'approved')
                    HAVING
                        distanceMeters <= {$distanceMax}
                    ORDER BY
                        events.event_date ASC
                ) as filtered
                GROUP BY
                    event_date
                LIMIT
                    {$offset}, {$this->limit}
            ")
        );

        $result = array_map(function($entry){
            return $entry->event_date;
        }, $dates);

        return $result;
    }

    public function getStickyEvents(): array
    {
        $currentUser = $this->currentUser;
        
        $eventsQuery = Event::selectRaw(
            "events.*, ST_Distance_sphere(ST_GeomFromText('point(".$this->currentUser->lat." ".$this->currentUser->lng.")', 4326), events.location_geom) AS distanceMeters"
        )
            ->withCount('likes')
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
            ->where('events.status', 'active')
            ->where('is_sticky', 1)
            ->whereHas('user', function ($query) {
                $query
                    ->where('status', 'active')
                    ->whereNull('deleted_at');
            })
            ->orderBy('event_date')
            ->orderBy('distanceMeters', 'asc');

        if ($this->blockedUsersIds) {
            $eventsQuery = $eventsQuery->whereNotIn('user_id', $this->blockedUsersIds);

        }

        if (!empty($this->except)) {
            $eventsQuery = $eventsQuery->whereNotIn('events.id', $this->except);
        }

        $events = $eventsQuery
            ->get()
            ->map(function ($event) {
                /** @var Event $event */
                return $event->getAttributesByMode(Event::ATTRIBUTES_MODE_DISCOVER, $this->currentUser);
            })
            ->groupBy('type')
            ->toArray();

        return $events;
    }

    protected function getEventsByDate(string $date, ?int $limit, int $distanceMin, int $distanceMax): array
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
            ->where('is_sticky', 0)
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

    protected function getEventsByDateCount(string $date, ?int $limit, int $distanceMin, int $distanceMax): int
    {
        $eventsQuery = Event::whereIn('status', [Event::STATUS_ACTIVE, Event::STATUS_APPROVED])
            ->where('type', $this->filterType)
            ->where('event_date', $date)
            ->where('is_sticky', 0)
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
