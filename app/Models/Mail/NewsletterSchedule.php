<?php namespace App\Models\Mail;

use Illuminate\Database\Eloquent\Model;

class NewsletterSchedule extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'newsletter_schedule';

    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject',
        'body',
        'in_process',
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
     * Get members
     */
    public function recipients()
    {
        return $this->hasManyThrough(
            'App\User',
            'App\Models\Mail\NewsletterScheduleMember',
            'schedule_id', // Foreign key on NewsletterScheduleMember table...
            'id', // Foreign key on User table...
            'id', // Local key on NewsletterSchedule table...
            'user_id' // Local key on NewsletterScheduleMember table...
        );
    }

}
