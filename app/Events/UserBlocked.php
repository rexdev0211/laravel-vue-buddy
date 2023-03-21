<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserBlocked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $broadcastQueue = 'UserBlocked';

    /**
     * @var int
     */
    public $userId;

    /**
     * @var int
     */
    public $recipientId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $conversations;

    /**
     * Create a new event instance.
     *
     * @var int $userId
     * @var int|null $recipientId
     * @var string $type
     * @var array|null $conversations
     * @return void
     */
    public function __construct(int $userId, int $recipientId, string $type, array $conversations = null)
    {
        $this->userId = $userId;
        $this->recipientId = $recipientId;
        $this->type = $type;
        $this->conversations = $conversations;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-' . $this->recipientId);
    }
}