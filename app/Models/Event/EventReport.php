<?php namespace App\Models\Event;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EventReport extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'event_id',
        'reason',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reporter() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event() {
        return $this->belongsTo('App\Event');
    }

    /**
     * Format Report for Adminpanel
     *
     * @var array
     */
    public function formatForAdminTopReports()
    {
        $return = [
            'reason'   => $this->reason,
            'user_id'  => $this->user_id,
            'owner'    => null,
            'event'    => (object)[
                'id'            => $this->event->id,
                'user_id'       => $this->event->user_id,
                'title'         => $this->event->title,
                'type'          => $this->event->type,
                'chemsfriendly' => $this->event->chemsfriendly,
                'status'        => $this->event->status,
            ],
        ];

        if (!empty($this->event->user)) {
            $return['owner'] = (object)[
                'id'   => $this->event->user->id,
                'name' => $this->event->user->name,
            ];
        }

        return (object)$return;
    }

    /**
     * Get Reports for Adminpanel
     *
     * @var array
     */
    public static function getReportsForAdmin($page, $perPage, $orderBy, $orderBySort) {
        if ($orderBy == 'idate') {
            $orderBy = 'created_at';
        }

        $reports = EventReport::with('event')
                              ->with('event.user')
                              ->with('reporter')
                              ->whereHas('event')
                              ->whereHas('event.user')
                              ->whereHas('reporter')
                              ->orderBy($orderBy, $orderBySort)
                              ->skip($perPage * ($page - 1))
                              ->limit($perPage)
                              ->paginate($perPage, ['*'], 'page', $page);


        $topCounters = EventReport::select('event_id')
                                  ->whereHas('event')
                              ->whereHas('event.user')
                              ->whereHas('reporter')
                                  ->selectRaw('COUNT(id) as counted')
                                  ->groupBy('event_id')
                                  ->limit(10)
                                  ->get();

        $topIds      = $topCounters->pluck('event_id');
        $topCounters = $topCounters->pluck('counted', 'event_id');

        $top = EventReport::select('event_id', 'reason')
                          ->selectRaw('COUNT(id) as counted')
                          ->whereHas('event')
                          ->whereHas('event.user')
                          ->whereHas('reporter')
                          ->with('event')
                          ->with('event.user')
                          ->whereIn('event_id', $topIds)
                          ->groupBy('event_id', 'reason')
                          ->orderBy('counted', 'DESC')
                          ->get()
                          ->map(function($item) use ($topCounters) {
                              $formatted = $item->formatForAdminTopReports();
                              $formatted->counted = $topCounters[$item->event_id];

                              return $formatted;
                          })
                          ->groupBy('event.id')
                          ->map(function($group) {
                              return $group->first();
                          })
                          ->sortByDesc('counted');
        return [
            'list' => $reports,
            'top'  => $top,
        ];
    }
}
