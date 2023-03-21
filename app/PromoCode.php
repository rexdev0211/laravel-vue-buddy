<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    /**
     * Table
     * @var string
     */
    protected $table = 'promo_codes';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'code',
        'title',
        'expiration_time',
        'months',
        'weeks',
        'days',
        'status',
        'limit',
        'used_count',
    ];

    /**
     * Dates
     * @var [type]
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'expiration_time',
    ];

    /**
     * Get PROmo time duration
     *
     * @return string
     */
    public function getDurationAttribute()
    {
        $duration = [];

        if ($this->months > 0) $duration[] = $this->months.' months';
        if ($this->weeks > 0)  $duration[] = $this->weeks.' weeks';
        if ($this->days > 0)   $duration[] = $this->days.' days';

        return implode(', ', $duration);
    }
}
