<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RefreshDataRequest implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $broadcastQueue = 'RefreshDataRequest';

    public $user_to;

    public $type;

    /**
     * Create a new event instance.
     *
     * @param int $user_to
     * @param string|null $type
     *
     * @return void
     */
    public function __construct(int $user_to, string $type = null)
    {
        $this->user_to = $user_to;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-' . $this->user_to);
    }
}
