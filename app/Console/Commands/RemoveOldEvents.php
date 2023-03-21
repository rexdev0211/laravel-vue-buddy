<?php

namespace App\Console\Commands;

use App\Event;
use App\EventMembership;
use App\Message;
use App\Models\Event\EventReport;
use App\Services\ChatService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RemoveOldEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:remove-old-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletion of all messages and events for the last 7 days';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentDate = Carbon::now();
        $currentGuideDate = $currentDate->subYear();
        $currentDate->subMonth();
        $currentDate = $currentDate->format('Y-m-d');
        $currentGuideDate = $currentGuideDate->format('Y-m-d');

        $eventQuery = Event::where(function ($sub) use ($currentDate){
            $sub->where('event_date','<', $currentDate);
            $sub->whereIn('type',[
                Event::TYPE_FUN,
                Event::TYPE_FRIENDS,
            ]);
        })->orWhere(function ($subQ) use ($currentGuideDate) {
            $subQ->where('event_date','<', $currentGuideDate);
            $subQ->where('type', Event::TYPE_GUIDE);
        });

        $eventsCount = $eventQuery->count();
        $this->info('Events to remove - ' . $eventsCount);

        $eventsChunk = $eventQuery->select('id', 'user_id')->get()->chunk(1000);

        $channel = Message::CHANNEL_EVENT;

        foreach ($eventsChunk as $events) {

            foreach ($events as $event) {

                $recipientsIds = Message::select('user_from', 'user_to')
                                        ->project(['_id' => 0])
                                        ->where('event_id', $event->id)
                                        ->get()
                                        ->toArray();

                $usersIds = [];

                foreach ($recipientsIds as $recipientsId) {
                    $usersIds = array_merge($usersIds, array_values($recipientsId));
                }

                (new ChatService())->removeCacheConversationForEventOrGroupRecipients($channel, $event->user_id, array_unique($usersIds), $event->id);

                Message::where('event_id', $event->id)->delete();
                EventReport::where('event_id', $event->id)->delete();
                EventMembership::where('event_id', $event->id)->delete();
            }

        }

        $eventQuery->delete();

        return true;
    }
}
