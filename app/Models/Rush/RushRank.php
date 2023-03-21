<?php namespace App\Models\Rush;

use Illuminate\Database\Eloquent\Model;

class RushRank extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rushes_ranks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rush_id',
        'strip_id',
        'applauses_count',
        'views_count',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get Rank Rush
     * @return [type] [description]
     */
    public function rush()
    {
        return $this->belongsTo(Rush::class, 'rush_id');
    }

}
