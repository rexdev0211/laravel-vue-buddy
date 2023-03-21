<?php namespace App\Models\Rush;

use Illuminate\Database\Eloquent\Model;

class RushView extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rushes_views';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'rush_id',
        'strip_id',
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
     * Set strip as Viewed for Authenticated user
     *
     * @var bool
     */
    public function stripViewed($stripId)
    {
        if ($this->strip_id < $stripId) {
            $this->strip_id = $stripId;

            RushView::where('rush_id', $this->rush_id)
                    ->where('user_id', $this->user_id)
                    ->update([
                        'strip_id' => $stripId,
                    ]);
        }

        return $this;
    }

    /**
     * Get Viewed Rush
     * @return [type] [description]
     */
    public function rush()
    {
        return $this->belongsTo(Rush::class, 'rush_id');
    }

}
