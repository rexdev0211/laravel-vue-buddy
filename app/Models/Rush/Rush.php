<?php namespace App\Models\Rush;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Rush extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rushes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'title',
        'status',
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
     * Get Rush Author
     * @return [type] [description]
     */
    public function author()
    {
        return $this->hasOne(\App\User::class, 'id', 'user_id');
    }

    /**
     * Get Rush Strips
     * @return [type] [description]
     */
    public function strips()
    {
        return $this->hasMany(RushStrip::class, 'rush_id')
                    ->where('is_deleted', 0)
                    ->orderBy('id', 'ASC');
    }

    /**
     * Get Latest Strip
     * @return [type] [description]
     */
    public function latest_strip()
    {
        return $this->hasOne(RushStrip::class, 'rush_id')
                    ->latest();
    }

    /**
     * Get Latest Viewed Strip for Authenticated user
     * @return [type] [description]
     */
    public function latest_viewed_strip()
    {
        return $this->hasOne(RushView::class, 'rush_id')
                    ->where('user_id', auth()->user()->id);
    }

    /**
     * Get Rush Favorites
     * @return [type] [description]
     */
    public function favorites()
    {
        return $this->hasMany(RushFavorite::class, 'rush_id');
    }

    /**
     * Get Rush Ranks
     * @return [type] [description]
     */
    public function ranks()
    {
        return $this->hasMany(RushRank::class, 'rush_id')
                    ->where('is_deleted', 0);
    }

    /**
     * Get Rush Favorite data
     * @return [type] [description]
     */
    public function getFavoriteAttribute()
    {
        return [
            'is_favorite' => $this->favorites->where('user_id', auth()->user()->id)->first() ? true : false,
            'total'       => $this->favorites->count(),
        ];
    }

    /**
     * Format data for view
     * @return array
     */
    public function formatForView($forView = false)
    {
        $formatted = [
            'id'           => $this->id,
            'title'        => $this->title,
            'latest_strip' => $this->latest_strip->formatForView(),
        ];

        if ($forView) {
            $formatted['applauses_count']     = $this->ranks->sum('applauses_count');
            $formatted['applauses_formatted'] = $formatted['applauses_count'] >= 1000 ? floor($formatted['applauses_count']/1000).'k' : $formatted['applauses_count'];
        }

        if (auth()->check() && auth()->user()->id == $this->user_id) {
            $full = auth()->user()->isPro() ? 72 : 24;

            $formatted['pie_part'] = floor($formatted['latest_strip']['active_hours'] / ($full / 9)) + 1;

            if ($formatted['pie_part'] > 9) $formatted['pie_part'] = 9;
        } else {
            $formatted['pie_part'] = false;
        }

        return $formatted;
    }

    /**
     * Update Rush Rank
     * @return array
     */
    public function updateRanks()
    {
        $strips = $this->strips()
                       ->with('applauses')
                       ->get();

        foreach ($strips as $strip) {
            $rank = RushRank::where('rush_id', $strip->rush_id)
                            ->where('strip_id', $strip->id)
                            ->first();

            if (!$rank) {
                $rank = new RushRank;
                $rank->rush_id     = $strip->rush_id;
                $rank->strip_id    = $strip->id;
                $rank->views_count = 0;
            }

            $rank->applauses_count = $strip->applauses->sum('applauses');
            $rank->save();
        }

        return $strips;
    }

    /**
     * Is Rush Strip favorite
     * @return [type] [description]
     */
    public function isFavoriteForUser($userId)
    {
        return RushFavorite::where('rush_id', $this->id)
                           ->where('user_id', $userId)
                           ->first();
    }

    /**
     * Toggle Favorite state for User
     * @return array
     */
    public function favoriteToggle($userId)
    {
        $favorite = $this->isFavoriteForUser($userId);

        $isFavorite = false;
        if ($favorite) {
            RushFavorite::where('rush_id', $this->id)
                        ->where('user_id', $userId)
                        ->delete();
        } else {
            $isFavorite = true;
            RushFavorite::create([
                'rush_id' => $this->id,
                'user_id' => $userId,
            ]);
        }

        return [
            'is_favorite' => $isFavorite,
            'total'       => $this->favorites->count(),
        ];
    }

    /**
     * Format data for adminpanel
     * @return array
     */
    public function formatForAdmin()
    {
        return (object)[
            'id'              => $this->id,
            'status'          => $this->status,
            'title'           => $this->title,
            'streak'          => Carbon::now()->diffInDays($this->created_at),
            'applauses_count' => $this->ranks->sum('applauses_count'),
            'views_count'     => $this->ranks->sum('views_count'),
            'favorites_count' => $this->favorites_count,
            'author'          => (object)[
                'id'   => $this->author->id,
                'name' => $this->author->name,
            ],
        ];
    }

    /**
     * Suspend Rush
     * @return array
     */
    public function suspend()
    {
        $this->status = 'suspended';
        $this->save();

        return $this;
    }

    /**
     * Activate Suspended Rush
     * @return array
     */
    public function activate()
    {
        $this->status = 'active';
        $this->save();

        return $this;
    }

    /**
     * Get favorites list and queue
     * @return array
     */
    public static function getFavoritesData()
    {
        $queue = [];

        $favorites = RushFavorite::with('rush')
                                 ->with('rush.author')
                                 ->with('rush.strips')
                                 ->with('rush.latest_viewed_strip')
                                 ->where('user_id', auth()->user()->id)
                                 ->whereHas('rush', function($query){
                                     $query->whereIn('status', ['active']);
                                 })
                                 ->get()
                                 ->map(function($item) {
                                     return $item->formatForView();
                                 })
                                 ->sortByDesc('latest_strip_id')
                                 ->values()
                                 ->sortByDesc('have_unviewed')
                                 ->values();

        foreach ($favorites as $favorite) {
            $queue[] = $favorite['id'];
        }

        return [
            'list'  => $favorites,
            'queue' => $queue,
        ];
    }

    /**
     * Get Strips main page grid, favorites list and queue
     * @return array
     */
    public static function getStripsAndFavoritesData()
    {
        $favorites = self::getFavoritesData();

        $queue = [
            'favorites' => $favorites['queue'],
            'rushes'    => [],
        ];

        $top = Rush::with('latest_strip')
                   ->with('ranks')
                   ->whereIn('status', ['active'])
                   ->latest()
                   ->get()
                   ->map(function($item) {
                       return $item->formatForView(true);
                   })
                   ->sortByDesc(function($item) {
                       return $item['applauses_count'];
                   })
                   ->values();

        // count how many chunks with 6 items we have
        $chunksCount = floor($top->count()/6);
        // group rushes
        $rushes = $top->splice($chunksCount)
                      ->sortByDesc(function($item){
                          return $item['latest_strip']['id'];
                      })
                      ->values()
                      ->chunk(5)
                      ->map(function($collection, $index) use ($top) {
                          return [
                              'group' => $collection->values(),
                              'best'  => isset($top[$index]) ? $top[$index] : null,
                          ];
                      });

        foreach ($rushes as $index => $group) {
            if ($index % 2 == 0) {
                $side = 'right';
            } else {
                $side = 'left';
            }

            foreach ($group['group'] as $key => $rush){
                // for left sided best square it should be first in group
                if ($side == 'left' && $group['best'] && $key == 0) {
                    $queue['rushes'][] = $group['best']['id'];
                }

                $queue['rushes'][] = $rush['id'];

                // for right sided best square it should be second in group
                if ($side == 'right' && $group['best'] && $key == 0) {
                    $queue['rushes'][] = $group['best']['id'];
                }
            }
        }

        return [
            'rushes'    => $rushes,
            'favorites' => $favorites['list'],
            'queue'     => $queue,
        ];
    }

    /**
     * Clear Strip favorites and ranks
     * @return array
     */
    public function clear()
    {
        RushRank::where('rush_id', $this->id)->delete();
        RushView::where('rush_id', $this->id)->delete();
        RushFavorite::where('rush_id', $this->id)->delete();

        return true;
    }
}
