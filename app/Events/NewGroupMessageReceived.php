<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewGroupMessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $broadcastQueue = 'NewGroupMessageReceived';

    public $broadcastingData;

    public $conversationData;

    /**
     * Create a new event instance.
     *
     * @param array $conversationData
     * @param array|null $broadcastingData
     *
     * @return void
     */
    public function __construct(array $conversationData, ?array $broadcastingData = [])
    {
        $this->conversationData = $conversationData;
        $this->broadcastingData = $broadcastingData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-group-chat-' . $this->conversationData['event']['id']);
    }
}
