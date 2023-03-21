<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CheckMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $broadcastQueue = 'CheckMessage';

    /**
     * @var array
     */
    public $messageData;

    /**
     * @var int
     */
    public $currentUserId;

    /**
     * @var int
     */
    public $eventId;

    /**
     * Create a new event instance.
     *
     * @param array $data
     * @param int $currentUserId
     * @param int|null $eventId
     * @return void
     */
    public function __construct(array $data = [], int $currentUserId, int $eventId = null)
    {
        $this->messageData = $data;
        $this->currentUserId = $currentUserId;
        $this->eventId = $eventId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-' . $this->currentUserId);
    }
}
