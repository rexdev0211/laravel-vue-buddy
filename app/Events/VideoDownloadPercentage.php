<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoDownloadPercentage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $broadcastQueue = 'VideoDownloadPercentage';
    /**
     * @var int
     */
    private $user_id;
    /**
     * @var float
     */
    public $percentage;
    /**
     * @var string
     */
    public $hash;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $user_id,string $hash, float $percentage)
    {
        $this->user_id = $user_id;
        $this->percentage = $percentage;
        $this->hash = $hash;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-' . $this->user_id);
    }
}
