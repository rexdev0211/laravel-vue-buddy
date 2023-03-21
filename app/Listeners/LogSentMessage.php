<?php

namespace App\Listeners;

use App\SentEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\InteractsWithQueue;

class LogSentMessage
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
     * @param  MessageSent  $event
     * @return void
     */
    public function handle(MessageSent $event)
    {
        /** @var SentEmail $checkLatest */
        $checkLatest = SentEmail::where('email', $event->message->getTo())->first();

        if (null !== $checkLatest) {
            $checkLatest->status = SentEmail::STATUS_SENT;
            $checkLatest->save();
            return;
        }

        SentEmail::create([
            'email' => (string) current($event->message->getTo()),
            'message' => (string) $event->message->getBody(),
            'status' => SentEmail::STATUS_SENT,
        ]);
    }
}
