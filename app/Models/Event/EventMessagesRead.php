<?php namespace App\Models\Event;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

class EventMessagesRead extends Model
{
    use HybridRelations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $collection = 'event_messages_read';

    /**
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * @var array[]
     */
    protected $guarded = ['_id'];

    /**
     * @var array
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'latest_read'
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['latest_read'];
}
