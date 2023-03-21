<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewVisitorReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $visitorData;

    public $broadcastQueue = 'NewVisitorReceived';

    /**
     * Create a new event instance.
     *
     * @param array $data
     *
     * @return void
     */
    public function __construct(array $data = [])
    {
        array_walk_recursive($data, function (&$entry) {
            $entry = mb_convert_encoding($entry, 'UTF-8');
        });

        $this->visitorData = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-' . $this->visitorData['visited_id']);
    }
}
