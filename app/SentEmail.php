<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SentEmail extends Model
{
    const STATUS_SENDING = 0;
    const STATUS_SENT = 1;

    /**
     * Table
     * @var string
     */
    protected $table = 'sent_emails';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'email',
        'message',
        'status',
    ];

    /**
     * Dates
     * @var [type]
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
