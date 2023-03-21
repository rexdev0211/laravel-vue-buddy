<?php namespace App\Models\Rush;

use App\UserPhoto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class RushStrip extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rushes_strips';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rush_id',
        'type',
        'message',
        'image_id',
        'video_id',
        'image_path',
        'video_path',
        'profile_attached',
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
     * Get Strip Rush
     * @return [type] [description]
     */
    public function rush()
    {
        return $this->belongsTo(Rush::class, 'rush_id');
    }

    /**
     * Get Strip Applauses
     * @return [type] [description]
     */
    public function applauses()
    {
        return $this->hasMany(RushApplause::class, 'strip_id');
    }

    /**
     * Get Strip Rank
     * @return [type] [description]
     */
    public function rank()
    {
        return $this->hasOne(RushRank::class, 'strip_id');
    }

    /**
     * Get Strip Applauses by Authenticated user
     * @return [type] [description]
     */
    public function getMyApplausesAttribute()
    {
        $myApplauses = $this->applauses
                            ->where('user_id', auth()->user()->id)
                            ->first();
        return $myApplauses ? $myApplauses->applauses : 0;
    }

    /**
     * Get Total Claps
     * @return [type] [description]
     */
    public function getTotalClapsAttribute()
    {
        $total = $this->rank ? $this->rank->applauses_count : 0;
        return $total >= 1000 ? floor($total/1000).'k' : $total;
    }

    /**
     * Get image formatted
     *
     * @var string
     */
    public function getImageFormattedAttribute() {
        return UserPhoto::getPhotoUrl($this->image_path, 'orig', $this->image_id ? 'rush' : 'users');
    }

    /**
     * Get created hours and minutes difference
     *
     * @var string
     */
    public function getActiveHoursAttribute() {
        return Carbon::now()->diffInHours($this->created_at);
    }

    /**
     * Format data for view
     * @return array
     */
    public function formatForView($forView = false)
    {
        $formatted = [
            'id'               => $this->id,
            'type'             => $this->type,
            'message'          => $this->message,
            'image'            => $this->type == 'image' ? $this->image_formatted : null,
            'image_id'         => $this->image_id,
            'image_path'       => $this->image_path,
            'profile_attached' => $this->profile_attached,
            'active_hours'     => $this->active_hours,
        ];

        if ($forView) {
            $formatted['my_applauses'] = $this->my_applauses;
            $formatted['total_claps']  = $this->total_claps;
        }

        return $formatted;
    }

    /**
     * Give applauses to Strip
     * @return array
     */
    public function applause($claps)
    {
        if ($claps > 10) $claps = 10;

        $applauses = RushApplause::where('rush_id', $this->rush_id)
                                 ->where('strip_id', $this->id)
                                 ->where('user_id', auth()->user()->id)
                                 ->first();
        if (!$applauses) {
            RushApplause::create([
                'rush_id'   => $this->rush_id,
                'strip_id'  => $this->id,
                'user_id'   => auth()->user()->id,
                'applauses' => $claps,
            ]);
        } else {
            RushApplause::where('rush_id', $this->rush_id)
                        ->where('strip_id', $this->id)
                        ->where('user_id', auth()->user()->id)
                        ->update([
                            'applauses' => $claps,
                        ]);
        }

        $this->rush->updateRanks();

        return $this;
    }

    /**
     * Mark Strip as viewed by user
     * @return array
     */
    public function markStripViewed($userId)
    {
        $view = RushView::where('rush_id', $this->rush_id)
                        ->where('user_id', $userId)
                        ->first();

        if (!$view) {
            RushView::create([
                'rush_id'  => $this->rush_id,
                'user_id'  => $userId,
                'strip_id' => $this->id,
            ]);
        } else {
            $view->stripViewed($this->id);
        }

        $rank = RushRank::where('rush_id', $this->rush_id)
                        ->where('strip_id', $this->id)
                        ->first();

        if (!$rank) {
            $rank = new RushRank;
            $rank->rush_id         = $this->rush_id;
            $rank->strip_id        = $this->id;
            $rank->views_count     = 0;
            $rank->applauses_count = 0;
        }

        $rank->views_count = $rank->views_count + 1;
        $rank->save();
    }

    /**
     * Clear Strip Slide applause and rank
     * @return array
     */
    public function clear($updateRank = false)
    {
        RushRank::where('strip_id', $this->id)->delete();
        RushApplause::where('strip_id', $this->id)->delete();

        return true;
    }

}
