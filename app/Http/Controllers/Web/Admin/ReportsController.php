<?php

namespace App\Http\Controllers\Web\Admin;

use Helper;
use App\Repositories\UserReportedRepository;
use App\Models\Event\EventReport;
use App\Http\Controllers\Web\Controller;

class ReportsController extends Controller
{
    private $userReportedRepository;

    public function __construct(UserReportedRepository $userReportedRepository)
    {
        $this->userReportedRepository = $userReportedRepository;
    }

    /**
     * @param UserReportedRepository $userReportedRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $sessionKey = 'admin.reports';

        $page        = (int) Helper::getUserPreference($sessionKey, 'page', 1);
        $perPage     = (int) Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());
        $orderBy     = Helper::getUserPreference($sessionKey, 'orderBy', 'idate');
        $orderBySort = Helper::getUserPreference($sessionKey, 'orderBySort', 'desc');

        $reports        = $this->userReportedRepository->getReportedUsersList($page, $perPage, $orderBy, $orderBySort);

        return view('admin.reports.index', [
            'reports'        => $reports,
            'sessionKey'     => $sessionKey,
        ]);
    }

    /**
     * @param $id
     */
    public function delete($id) {
        $this->userReportedRepository->deleteById($id);

        return \Redirect::route('admin.reports');
    }

    /**
     * @param $id
     */
    public function clearUserReports($id)
    {
        $this->userReportedRepository->clearUserReports($id);

        return \Redirect::route('admin.reports');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function events()
    {
        $sessionKey = 'admin.reports.events';

        $page        = (int)Helper::getUserPreference($sessionKey, 'page', 1);
        $perPage     = (int)Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());
        $orderBy     = Helper::getUserPreference($sessionKey, 'orderBy', 'idate');
        $orderBySort = Helper::getUserPreference($sessionKey, 'orderBySort', 'desc');

        $reports     = EventReport::getReportsForAdmin($page, $perPage, $orderBy, $orderBySort);

        $reports['sessionKey'] = $sessionKey;

        return view('admin.reports.events', $reports);
    }

    /**
     * Delete Event Report
     * @param $id
     */
    public function deleteEventReport($reportId)
    {
        EventReport::where('id', $reportId)->delete();

        return redirect(route('admin.reports.events'));
    }

    /**
     * Delete all Reports for Event
     * @param $id
     */
    public function clearEventsReports($eventId)
    {
        EventReport::where('event_id', $eventId)->delete();

        return redirect(route('admin.reports.events'));
    }
}
