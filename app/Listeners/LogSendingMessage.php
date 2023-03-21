<?php

namespace App\Listeners;

use App\SentEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Queue\InteractsWithQueue;

class LogSendingMessage
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MessageSending  $event
     * @return void
     */
    public function handle(MessageSending $event)
    {
        SentEmail::create([
            'email' => (string) current($event->message->getTo()),
            'message' => (string) $event->message->getBody(),
            'status' => SentEmail::STATUS_SENDING,
        ]);
    }
}
