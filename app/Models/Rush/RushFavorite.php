<?php namespace App\Models\Rush;

use Illuminate\Database\Eloquent\Model;

class RushFavorite extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rushes_favorites';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rush_id',
        'user_id',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the model should be incremented.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Get Favorite Rush
     * @return [type] [description]
     */
    public function rush()
    {
        return $this->belongsTo(Rush::class, 'rush_id');
    }

    /**
     * Format data for view
     * @return array
     */
    public function formatForView()
    {
        $latestViewedStripId = $this->rush->latest_viewed_strip ? $this->rush->latest_viewed_strip->strip_id : 0;
        $latest_strip        = $this->rush->strips->last();
        $count_unviewed      = $this->rush->strips->filter(function($strip) use ($latestViewedStripId) {
            return $strip->id > $latestViewedStripId;
        })->count();

        return [
            'id'              => $this->rush->id,
            'author'          => $this->rush->author->formatForRush(),
            'have_unviewed'   => $latestViewedStripId < $latest_strip->id ? 1 : 0,
            'count_unviewed'  => $count_unviewed,
            'latest_strip_id' => $latest_strip->id,
            'latest_strip'    => $latest_strip->formatForView(),
        ];
    }

}
