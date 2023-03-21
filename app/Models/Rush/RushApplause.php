<?php namespace App\Models\Rush;

use Illuminate\Database\Eloquent\Model;

class RushApplause extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rushes_applauses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rush_id',
        'strip_id',
        'user_id',
        'applauses',
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
     * Get Applauded Rush
     * @return [type] [description]
     */
    public function rush()
    {
        return $this->belongsTo(Rush::class, 'rush_id');
    }

}
