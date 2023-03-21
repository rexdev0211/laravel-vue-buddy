<?php namespace App\Repositories;

use App\UserReported;

class UserReportedRepository extends BaseRepository
{
    public function __construct(UserReported $model = null)
    {
        if (empty($model)){
            $model = new UserReported();
        }
        parent::__construct($model);
    }

    /**
     * @return mixed
     */
    public function getReportedUsersList($page, $perPage, $orderBy, $orderBySort)
    {
        return UserReported::with('reporter', 'reported')
                           ->whereHas('reported', function($query) {
                               $query->whereNull('deleted_at')
                                     ->where('status', 'active');
                           })
                           ->select('id', 'user_id', 'user_reported_id', 'report_type', 'idate')
                           ->selectRaw("(SELECT COUNT(by_type.id)
                                FROM user_reported_map AS by_type
                                WHERE by_type.user_reported_id = user_reported_map.user_reported_id
                                  AND by_type.report_type = user_reported_map.report_type) AS reports_same_count,
                                (SELECT COUNT(by_total.id)
                                     FROM user_reported_map AS by_total
                                     WHERE by_total.user_reported_id = user_reported_map.user_reported_id) AS reports_total_count")
                           ->orderBy($orderBy, $orderBySort)
                           ->orderBy('user_reported_id', 'DESC')
                           ->orderBy('idate', 'DESC')
                           ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function clearUserReports($userId)
    {
        return $this->whereUserReportedId($userId)->delete();
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function reportUser($userId, $reportedUserId, $reportType) {
        return $this->firstOrCreate([
            'user_id' => $userId,
            'user_reported_id' => $reportedUserId,
            'report_type' => $reportType
        ]);
    }

    /**
     * @param $userId
     * @param $type
     * @return mixed
     */
    public function gerReportsNumber($userId, $type) {
        return $this->where('user_reported_id', $userId)->where('report_type', $type)->count();
    }

}
