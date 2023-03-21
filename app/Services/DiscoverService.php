<?php

namespace App\Services;

use App\Facades\Helper;
use App\Repositories\MessageRepository;
use App\Tag;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;

class DiscoverService
{
    /** @var User */
    protected $currentUser;

    /** @var string */
    protected $filterType;

    /** @var bool */
    protected $onlyOnline;

    /** @var bool|null */
    protected $filterPics;

    /** @var bool|null */
    protected $filterVideos;

    /** @var string|null */
    protected $filterName;

    /** @var string|null */
    protected $filterTags;

    /** @var string|null */
    protected $filterAge;

    /** @var string|null */
    protected $filterPosition;

    /** @var string|null */
    protected $filterHeight;

    /** @var string|null */
    protected $filterWeight;

    /** @var string|null */
    protected $filterBody;

    /** @var string|null */
    protected $filterPenis;

    /** @var string|null */
    protected $filterDrugs;

    /** @var string|null */
    protected $filterHiv;

    /** @var int */
    protected $distance;

    /** @var int */
    protected $page;

    /** @var int */
    protected $perPage;

    /** @var float */
    protected $latitude;

    /** @var float */
    protected $longitude;

    /**
     * @return array
     */
    public function getRangedUsersAround(): array
    {
        // Bypass filters if it's a search
        if ($this->filterName || $this->filterTags) {
            $this->setOnlyOnline(null);
            $this->setFilterType('nearby');
        }

        if ($this->filterType == 'recent') {
            $this->setPerPage(99);
            $usersAround = $this->getUsersAround();

            if (count($usersAround) < 9) {
                $this->setDistance(250);
                $usersAround = $this->getUsersAround();
            }
        } elseif ($this->filterType == 'favorites') {
            $this->setDistance(null);
            $usersAround = $this->getUsersAround();
        } else {
            // Search within 50km around
            $usersAround = $this->getUsersAround();

            if (
                $this->distance
                &&
                count($usersAround) < $this->perPage
            ) {
                // Try again within 500km around
                $this->setDistance($this->distance * 10);
                $usersAround = $this->getUsersAround();

                if (
                    $this->distance
                    &&
                    count($usersAround) < $this->perPage
                ) {
                    // You're at the North Pole? Make a new try!
                    $this->setDistance(null);
                    $usersAround = $this->getUsersAround();
                }
            }
        }

        return [
            'usersAround' => $usersAround,
            'distance' => $this->distance
        ];
    }

    /**
     * @return array
     */
    public function getUsersAround(): array
    {
        $query = User::query()->where(function ($query) {
            $query->where(function ($query) {
                $query->where(function ($query) {
                    if ($this->filterType == 'nearby' && (!$this->filterName && !$this->filterTags)) {
                        $query->where('pro_expires_at', '>', now()->format('Y-m-d H:i:s'))
                              ->where('discreet_mode', '=', 0);
                    } else {
                        $query->where('pro_expires_at', '>', now()->format('Y-m-d H:i:s'))
                              ->where(function ($query) {
                                  $query->where(function ($query) {
                                      $query->where('discreet_mode', '=', 1)
                                            ->where('invisible', '=', 0);
                                  })->orWhere(function ($query) {
                                      $query->where('discreet_mode', '=', 0);
                                  });
                              });
                    }
                })->orWhere(function ($query) {
                    $query->where('pro_expires_at', '<=', now()->format('Y-m-d H:i:s'))
                          ->orWhereNull('pro_expires_at');
                });
            });
        });

        if ($this->onlyOnline) {
            $query->leftJoin('countries', 'users.country_code', '=', 'countries.code')
                ->whereRaw("DATE_ADD(`users`.`last_active`, INTERVAL (CASE WHEN `users`.`country_code` is NULL OR `users`.`country_code` = '' THEN " . config('const.USER_WAS_RECENTLY_MINUTES') * 60 . " ELSE `countries`.`was_recently_online_time` END) SECOND) > CURRENT_TIMESTAMP()");
        }

        if ($this->filterPosition) {
            $filterPositionArray = explode(',', $this->filterPosition);
            if (count($filterPositionArray)) {
                $query->whereIn('position', $filterPositionArray);
            }
        }

        if ($this->filterBody) {
            $filterBodyArray = explode(',', $this->filterBody);
            if (count($filterBodyArray)) {
                $query->whereIn('body', $filterBodyArray);
            }
        }

        if ($this->filterPenis) {
            $filterPenisArray = explode(',', $this->filterPenis);
            if (count($filterPenisArray)) {
                $query->whereIn('penis', $filterPenisArray);
            }
        }

        if ($this->filterDrugs) {
            $filterDrugsArray = explode(',', $this->filterDrugs);
            if (count($filterDrugsArray)) {
                $query->whereIn('drugs', $filterDrugsArray);
            }
        }

        if ($this->filterHiv) {
            $filterHivArray = explode(',', $this->filterHiv);
            if (count($filterHivArray)) {
                $query->whereIn('hiv', $filterHivArray);
            }
        }

        if ($this->filterPics) {
            $query->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('user_photos')
                    ->where('visible_to', 'public')
                    ->whereRaw('user_photos.user_id = users.id');
            });
        }

        if ($this->filterVideos) {
            $query->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('user_videos')
                    ->where('visible_to', 'public')
                    ->whereRaw('user_videos.user_id = users.id');
            });
        }

        if ($this->filterAge) {
            $filterAgeArray = explode(',', $this->filterAge);
            if (count($filterAgeArray) == 2) {
                $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) between ? and ? ', $filterAgeArray);
            }
        }

        if ($this->filterHeight) {
            $filterHeightArray = explode(',', $this->filterHeight);
            if (count($filterHeightArray) == 2) {
                $query->whereBetween('height', $filterHeightArray);
            }
        }

        if ($this->filterWeight) {
            $filterWeightArray = explode(',', $this->filterWeight);
            if (count($filterWeightArray) == 2) {
                $query->whereBetween('weight', $filterWeightArray);
            }
        }

        if ($this->filterType == 'favorites') {
            $joinFavorites = 'join';
        } else {
            $joinFavorites = 'leftJoin';
        }

        // We have 25km limit for new users AND display only 99 users AND max 1 month
        if ($this->filterType == 'recent') {
            $query->where('created_at', '>', now()->subMonth(1));
        }

        $query->$joinFavorites('user_favorites_map', function ($query) {
            $query->whereRaw(' `user_favorites_map`.`user_favorite_id` = `users`.`id`')
                  ->where('user_favorites_map.user_id', '=', $this->currentUser->id);
        });

        $latitude  = $this->currentUser->lat;
        $longitude = $this->currentUser->lng;

        // Disable distance check for a while
        if (
            $this->distance
            &&
            $this->filterType != 'favorites'
        ) {
            $maxDistance = $this->distance * 1000;

            $latMin = $latitude - ($this->distance / 111.045);
            $latMax = $latitude + ($this->distance / 111.045);

            $lngMin = $longitude - ($this->distance / abs(111.045 * cos(deg2rad($latitude))));
            $lngMax = $longitude + ($this->distance / abs(111.045 * cos(deg2rad($latitude))));

            if ($lngMin < -180) {
                $lngArray = [
                    ['from' => -180, 'to' => $lngMax],
                    ['from' => 360 + $lngMin, 'to' => 180]
                ];
            } elseif ($lngMax > 180) {
                $lngArray = [
                    ['from' => $lngMin, 'to' => 180],
                    ['from' => -180, 'to' => $lngMax - 360]
                ];
            } else {
                $lngArray = [
                    ['from' => $lngMin, 'to' => $lngMax]
                ];
            }

            // Lat radius
            $query->whereRaw('lat BETWEEN ? AND ?', [$latMin, $latMax]);

            // Lng radius
            $query->where(function ($q) use ($lngArray) {
                foreach ($lngArray as $item) {
                    $q->orWhereRaw('lng BETWEEN ? AND ?', [$item['from'], $item['to']]);
                }
            });

            // Distance
            $query->having('distanceMeters', '<', $maxDistance);
        }

        // Exclude blocked users
        $query->whereNotExists(function ($query) {
            $query->select(\DB::raw(1))
                ->from('user_blocked_map')
                ->whereRaw('user_id = ? and user_blocked_id = users.id', [$this->currentUser->id]);
        });
        $query->whereNotExists(function ($query) {
            $query->select(\DB::raw(1))
                ->from('user_blocked_map')
                ->whereRaw('user_blocked_id = ? and user_id = users.id', [$this->currentUser->id]);
        });

        // Show all groups in search mode
        if ($this->filterName) {
            $query->where('name', 'like', "%{$this->filterName}%");
        }

        if ($this->filterTags) {
            $searchRequest = trim($this->filterTags);
            preg_match_all('/\#(.+)/', $searchRequest, $tagsArray); // '/\#([^\s]+)/'
            $tagsList = [];

            if (!empty($tagsArray[1])) {
                $tagsArray = $tagsArray[1];
                $tagsList = [];

                $spamService = new SpamService();

                foreach ($tagsArray as $tag) {
                    $tag = $spamService->replaceRestrictedWords($tag);

                    if (empty($tag)) {
                        continue;
                    }

                    $checkTagsExists = Tag::where(function ($q) use ($tag) {
                        $q->where('name', 'like', $tag)
                            ->orWhere('name', 'like', $tag.' %');
                    })
                        ->with(['users'])
                        ->get();

                    /** @var Tag $checkTagExist */
                    foreach ($checkTagsExists as $checkTagExist) {
                        $tagsList[] = $checkTagExist->id;
                    }
                }
            }

            $query->whereHas('tags', function ($q) use ($tagsList) {
                $q->whereIn('id', $tagsList);
            });
        }

        // By default we show only 'member' group, except for search
        if (!$this->filterName && !$this->filterTags) {
            $query->where('user_group', User::GROUP_MEMBER);
        }

        $query
            ->selectRaw(
                "
                users.id, 
                users.link, 
                users.name, 
                users.photo, 
                users.dob, 
                users.last_active, 
                users.height, 
                users.weight,
                users.position, 
                users.show_age,
                users.locality, 
                users.state, 
                users.country, 
                users.country_code,
                users.body, 
                users.deleted_at, 
                users.status, 
                users.discreet_mode, 
                users.invisible, 
                users.user_group, 
                users.pro_expires_at,
                user_favorites_map.user_favorite_id,
                ST_Distance_sphere(ST_GeomFromText('point(".$latitude." ".$longitude.")', 4326), users.location) AS distanceMeters"
            );

        if ($this->currentUser->isGhosted()) {
            $query->whereIn('status', ['active', 'ghosted']);
        } else {
            $query->where('status', 'active');
        }

        $query->whereNull('deleted_at')->forPage($this->page + 1, $this->perPage);

        if ($this->filterType == 'nearby') {
            $query
                ->orderByRaw('users.id = ' . $this->currentUser->id . ' desc')
                ->orderBy('distanceMeters', 'asc')
                ->orderBy('users.id', 'asc');
        } elseif ($this->filterType == 'favorites') {
            $query
                ->orderByRaw('ISNULL(user_favorite_id) asc')
                ->orderBy('distanceMeters', 'asc')
                ->orderBy('users.id', 'asc');
        } elseif ($this->filterType == 'recent') {
            $query->orderBy('users.id', 'desc');
        }

        $users = $query->get();

        /* Get dialog */
        $unreadMessagesStat = collect((new MessageRepository())->getAllCachedConversations($this->getCurrentUser()))->where('chatType', '=', 'user')->pluck('unreadMessagesCount', 'interlocutor.id')->toArray();

        $users = $users->map(function (User $user) use ($unreadMessagesStat) {
            $userData = $user->getAttributesByMode(User::ATTRIBUTES_MODE_DISCOVER, $this->currentUser);
            $userData['unreadMessagesCount'] = $unreadMessagesStat[$user->id] ?? 0;

            return $userData;
        })
        ->toArray();
        
        if (\Helper::isApp()) {
            return $users;
        } else {
            return [
                'users' => $users,
                'unreadMessagesStat' => $unreadMessagesStat,
            ];
        }
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
     * @return bool|null
     */
    public function isOnlyOnline(): ?bool
    {
        return $this->onlyOnline;
    }

    /**
     * @param bool|null $onlyOnline
     */
    public function setOnlyOnline(?bool $onlyOnline): void
    {
        $this->onlyOnline = $onlyOnline;
    }

    /**
     * @return bool|null
     */
    public function getFilterPics(): ?bool
    {
        return $this->filterPics;
    }

    /**
     * @param bool|null $filterPics
     */
    public function setFilterPics(?bool $filterPics): void
    {
        $this->filterPics = $filterPics;
    }

    /**
     * @return bool|null
     */
    public function getFilterVideos(): ?bool
    {
        return $this->filterVideos;
    }

    /**
     * @param bool|null $filterVideos
     */
    public function setFilterVideos(?bool $filterVideos): void
    {
        $this->filterVideos = $filterVideos;
    }

    /**
     * @return string|null
     */
    public function getFilterName(): ?string
    {
        return $this->filterName;
    }

    /**
     * @return string|null
     */
    public function getFilterTags(): ?string
    {
        return $this->filterTags;
    }

    /**
     * @param string|null $searchInput
     */
    public function setFilterTags(?string $searchInput): void
    {
        $this->filterTags = $searchInput;
    }

    /**
     * @param string|null $filterName
     */
    public function setFilterName(?string $filterName): void
    {
        $this->filterName = $filterName;
    }

    /**
     * @return string|null
     */
    public function getFilterAge(): ?string
    {
        return $this->filterAge;
    }

    /**
     * @param string|null $filterAge
     */
    public function setFilterAge(?string $filterAge): void
    {
        $this->filterAge = $filterAge;
    }

    /**
     * @return string|null
     */
    public function getFilterPosition(): ?string
    {
        return $this->filterPosition;
    }

    /**
     * @param string|null $filterPosition
     */
    public function setFilterPosition(?string $filterPosition): void
    {
        $this->filterPosition = $filterPosition;
    }

    /**
     * @return string|null
     */
    public function getFilterHeight(): ?string
    {
        return $this->filterHeight;
    }

    /**
     * @param string|null $filterHeight
     */
    public function setFilterHeight(?string $filterHeight): void
    {
        $this->filterHeight = $filterHeight;
    }

    /**
     * @return string|null
     */
    public function getFilterWeight(): ?string
    {
        return $this->filterWeight;
    }

    /**
     * @param string|null $filterWeight
     */
    public function setFilterWeight(?string $filterWeight): void
    {
        $this->filterWeight = $filterWeight;
    }

    /**
     * @return string|null
     */
    public function getFilterBody(): ?string
    {
        return $this->filterBody;
    }

    /**
     * @param string|null $filterBody
     */
    public function setFilterBody(?string $filterBody): void
    {
        $this->filterBody = $filterBody;
    }

    /**
     * @return string|null
     */
    public function getFilterPenis(): ?string
    {
        return $this->filterPenis;
    }

    /**
     * @param string|null $filterPenis
     */
    public function setFilterPenis(?string $filterPenis): void
    {
        $this->filterPenis = $filterPenis;
    }

    /**
     * @return string|null
     */
    public function getFilterDrugs(): ?string
    {
        return $this->filterDrugs;
    }

    /**
     * @param string|null $filterDrugs
     */
    public function setFilterDrugs(?string $filterDrugs): void
    {
        $this->filterDrugs = $filterDrugs;
    }

    /**
     * @return string|null
     */
    public function getFilterHiv(): ?string
    {
        return $this->filterHiv;
    }

    /**
     * @param string|null $filterHiv
     */
    public function setFilterHiv(?string $filterHiv): void
    {
        $this->filterHiv = $filterHiv;
    }

    /**
     * @return int|null
     */
    public function getDistance(): ?int
    {
        return $this->distance;
    }

    /**
     * @param int|null $distance
     */
    public function setDistance(?int $distance): void
    {
        $this->distance = $distance;
    }

    /**
     * @return array
     */
    public function getExceptIds(): array
    {
        return $this->exceptIds;
    }

    /**
     * @param array $exceptIds
     */
    public function setExceptIds(array $exceptIds): void
    {
        $this->exceptIds = $exceptIds;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @param int $perPage
     */
    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
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
}
