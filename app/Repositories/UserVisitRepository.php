<?php namespace App\Repositories;

use App\User;
use App\UserVisit;
use DB;

class UserVisitRepository extends BaseRepository
{
    public function __construct(UserVisit $model = null)
    {
        if (empty($model)) {
            $model = new UserVisit();
        }
        parent::__construct($model);
    }

    /**
     * @param int $visitorId
     * @param int $visitedId
     * @param bool $invisible
     *
     * @return UserVisit
     */
    public function createUserVisit(int $visitorId, int $visitedId, bool $invisible = false): UserVisit
    {
        $data = [
            'visitor_id' => $visitorId,
            'visited_id' => $visitedId,
            'invisible' => $invisible ? 1 : 0,
        ];

        /** @var UserVisit $visit */
        $visit = $this->create($data);

        return $visit;
    }

    /**
     * @param User $user
     * @param int $perPage
     * @param int $lastVisitId
     * @param int $minVisitId
     *
     * @return array
     */
    public function getUserVisitors(User $user, int $perPage = 25, int $lastVisitId = 0, int $minVisitId = 0): array
    {
        $visitIds = $this->getVisitEntriesByMode('visitors', ...func_get_args());
        $userVisitors = $this
            ->model
            ->whereIn('id', $visitIds)
            ->orderBy('id', 'desc')
            ->limit($perPage)
            ->get();

		$userVisitorsProduced = $userVisitors->map(function ($visit) use ($user) {
            /** @var User $visitor */
            $visitor = $visit->visitor;
            $visit = $visit->toArray();
            $visit['visitor'] = $visitor->getAttributesByMode(User::ATTRIBUTES_MODE_GENERAL, $user);
            return $visit;
		})
		->toArray();
		
		return $userVisitorsProduced;
    }

    /**
     * @param User $user
     * @param int $perPage
     * @param int $maxVisitId
     * @param int $minVisitId
     *
     * @return array
     */
    public function getVisitedUsers(User $user, int $perPage = 25, int $maxVisitId = 0, int $minVisitId = 0): array
    {
        $visitIds = $this->getVisitEntriesByMode('visited', ...func_get_args());
        $visitedUsers = $this->model
            ->whereIn('id', $visitIds)
            ->orderBy('id', 'desc')
            ->limit($perPage)
            ->get();
			
		$visitedUsersProduced = $visitedUsers->map(function ($visit) use ($user) {
            /** @var User $visitedUser */
            $visitedUser = $visit->visited;
            $visit = $visit->toArray();
            $visit['visited'] = $visitedUser->getAttributesByMode(User::ATTRIBUTES_MODE_GENERAL, $user);
			return $visit;
		})
		->toArray();
		
		return $visitedUsersProduced;
    }

    /**
     * @param string $mode
     * @param User $user
     * @param int $perPage
     * @param int $maxVisitId
     * @param int $minVisitId
     *
     * @return array
     */
    protected function getVisitEntriesByMode(string $mode, User $user, int $perPage = 25, int $maxVisitId = 0, int $minVisitId = 0): array
    {
        switch ($mode) {
            case 'visitors':{
                $externalPointer = 'visitor_id';
                $internalPointer = 'visited_id';
                break;
            }
            case 'visited':{
                $externalPointer = 'visited_id';
                $internalPointer = 'visitor_id';
                break;
            }
            default:{
                throw new \Error("Unknown visit entries mode: $mode");
            }
        }

        // User`s visitors:
        // - Ghosted users can see visits of active and ghosted users
        // - Active users can see visits only of active users
        // Visited users:
        // - Ghosted users can see active and ghosted users as visited
        // - Active users can see only active users as visited
        $allowedStatuses = $user->isGhosted() ?
            "'active', 'ghosted'"
            :
            "'active'";

        $maxIdClause = '';
        if (!empty($maxVisitId)) {
            $maxIdClause = " AND visits.id < $maxVisitId ";
        }

        $minIdClause = '';
        if (!empty($minVisitId)) {
            $minIdClause = " AND visits.id > $minVisitId ";
        }

        $userId = $user->id;

        $visitIds = DB::select(
            DB::raw("
                SELECT
                    MAX(visits.id) as id
                FROM
                    user_visits_map AS visits
                LEFT JOIN
                    # I blocked him
                    user_blocked_map AS i_blocked_entry ON visits.{$externalPointer} = i_blocked_entry.user_blocked_id and i_blocked_entry.user_id = {$userId}
                LEFT JOIN
                    # I was blocked by him
                    user_blocked_map AS blocked_me_entry ON visits.{$externalPointer} = blocked_me_entry.user_id and blocked_me_entry.user_blocked_id = {$userId}
                LEFT JOIN
                    # Retrieve users in order to check visitor status
                    users ON visits.{$externalPointer} = users.id
                WHERE
                    # Show only unmasked entries
                    visits.invisible = 0
                    AND
                    visits.{$internalPointer} = {$userId}
                    AND
                    # Include entries by visitors/visited status
                    users.status IN({$allowedStatuses})
                    AND
                    # Include entries by visitors/visited user deleted_at
                    users.deleted_at IS NULL
                    AND
                    # Exclude visitors/visited user blocked
                    i_blocked_entry.user_blocked_id IS NULL
                    AND
                    # Exclude visitors/visited who blocked me
                    blocked_me_entry.user_id IS NULL
                    # Pagination
                    {$maxIdClause}
                    {$minIdClause}
                GROUP BY
                    {$externalPointer}
                ORDER BY
                    id DESC   
                LIMIT {$perPage}
            ")
        );

        return collect($visitIds)
            ->pluck('id')
            ->toArray();
    }
}
