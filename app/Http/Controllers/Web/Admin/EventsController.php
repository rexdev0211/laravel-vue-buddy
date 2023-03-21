<?php

namespace App\Http\Controllers\Web\Admin;

use App\Events\RefreshDataRequest;
use Helper;
use App\Event;
use App\Models\Event\EventReport;
use App\Repositories\EventRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\Controller;
use Illuminate\View\View;

class EventsController extends Controller
{
    private $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param EventRepository $eventRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $sessionKey = 'admin.events';

        $page        = (int) Helper::getUserPreference($sessionKey, 'page', 1);
        $perPage     = (int) Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());
        $orderBy     = Helper::getUserPreference($sessionKey, 'orderBy', 'event_date');
        $orderBySort = Helper::getUserPreference($sessionKey, 'orderBySort', 'asc');

        $resetForm       = $request->exists('resetFilters');
        $filterTitle     = Helper::getUserPreference($sessionKey, 'filterTitle', '', $resetForm);
        $filterId        = Helper::getUserPreference($sessionKey, 'filterId', '', $resetForm);
        $filterType      = Helper::getUserPreference($sessionKey, 'filterType', '', $resetForm);
        $filterStatus    = Helper::getUserPreference($sessionKey, 'filterStatus', '', $resetForm);
        $filterOccur     = Helper::getUserPreference($sessionKey, 'filterOccur', 'future', $resetForm);
        $filterOwnerId   = Helper::getUserPreference($sessionKey, 'filterOwnerId', '', $resetForm);
        $filterOwnerName = Helper::getUserPreference($sessionKey, 'filterOwnerName', '', $resetForm);

        $events = $this->eventRepository->getEventsList($page, $perPage, $orderBy, $orderBySort, $filterTitle, $filterId, $filterStatus, $filterOccur, $filterOwnerName, $filterOwnerId, $filterType);

        $statusOptions = [
            '' => '--- Event status ---',
            'active' => 'Active events',
            'suspended' => 'Suspended events',
        ];

        $occurOptions = [
            'all' => '--- Occurrence---',
            'future' => 'Future events',
            'past' => 'Past events'
        ];

        $typeOptions = [
            ''                  => '--- Event Category ---',
            Event::TYPE_GUIDE   => 'Guide',
            Event::TYPE_FUN     => 'Fun',
            Event::TYPE_FRIENDS => 'Friends',
            Event::TYPE_BANG    => 'Bang',
        ];

        return view('admin.events.index', compact('events', 'typeOptions', 'statusOptions', 'occurOptions', 'sessionKey'));
    }

    /**
     * @param $id
     */
    public function view($id) {
        return view('admin.events.view', [
            'event' => $this->eventRepository->findEventWithAttachments($id),
        ]);
    }

    /**
     * @param $id
     */
    public function delete($id) {
        $this->eventRepository->deleteById($id);
        EventReport::where('event_id', $id)->delete();
        return redirect()->back();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suspend($id) {
        $this->eventRepository->update($id, ['status' => 'suspended']);

        return redirect()->back();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate($id) {
        $event = Event::find($id);

        if ($event->type === Event::TYPE_GUIDE) {
            $this->eventRepository->update($id, ['status' => Event::STATUS_APPROVED]);
        } else {
            $this->eventRepository->update($id, ['status' => 'active']);
        }

        return redirect()->back();
    }

    /**
     * Change event type
     *
     * @param integer $id
     * @param string $type
     *
     * @return RedirectResponse
     */
    public function changeType(int $id, string $type): RedirectResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return abort(404);
        }

        if ($type == Event::TYPE_FRIENDS) {
            $event->type = Event::TYPE_FRIENDS;
            $event->chemsfriendly = 0;

        } elseif ($type == Event::TYPE_FUN || $type == Event::TYPE_FUN_CHEMS_FRIENDLY) {
            $event->type = Event::TYPE_FUN;

            if ($type == Event::TYPE_FUN_CHEMS_FRIENDLY) {
                $event->chemsfriendly = 1;
            } else {
                $event->chemsfriendly = 0;
            }
        }

        $event->save();

        return redirect()->back();
    }

    /**
     * @return view
     */
    public function submissions(): view
    {
        $sessionKey = 'admin.events.submissions';

        $page        = (int) Helper::getUserPreference($sessionKey, 'page', 1);
        $perPage     = (int) Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());

        $events = $this->eventRepository->getEventsSubmissions($page, $perPage);

        return view('admin.eventsSubmissions.index', compact('page', 'perPage', 'events'));
    }

    /**
     * @param $id
     * @param $status
     * @return RedirectResponse
     */
    public function approveGuideEvent($id): RedirectResponse
    {
        $event = Event::find($id);
        $event->status = Event::STATUS_APPROVED;
        $event->save();

        event(new RefreshDataRequest($event->user_id, 'approvedEvent'));

        return redirect()->back();
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function declineGuideEvent($id): RedirectResponse
    {
        $event = Event::find($id);
        $event->status = Event::STATUS_DECLINED;
        $event->featured = 'no';
        $event->save();

        event(new RefreshDataRequest($event->user_id, 'declineGuideEvent'));

        return redirect()->back();
    }

    /**
     * @param $id
     * @param $feature
     * @return RedirectResponse
     */
    public function setFeaturedOrUnfeatured($id, $feature): RedirectResponse
    {
        $event = Event::find($id);

        if ($event->status !== Event::STATUS_ACTIVE) {
            $event->status = Event::STATUS_ACTIVE;
        }

        $event->featured = $feature;
        $event->save();

        event(new RefreshDataRequest($event->user_id, $feature === 'yes' ? 'featuredGuide' : 'unfeaturedGuide'));

        return redirect()->back();
    }

}
