<?php

namespace App\Console\Commands;

use App\Repositories\MessageRepository;
use Illuminate\Console\Command;

class PushNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:send-push-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send push notifications for apps every 5 minutes';

    private $messageRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //the only problem is when sending immediately the push notification is that we won't know for sure
        // if user closed the app or is still active in that 2 minute time frame between "last active" request
        // so those push notifications won't be sent
        return 'no need for this cron - at least for now';

        //send push notification
        $toUser = $userRepository->findUser($toUserId);

        $playerIds = $toUser->onesignalPlayers->pluck('player_id')->toArray();

        $pushMessage = $currentUser->name . ': ' . str_limit($userMessage, 50);

//        $response = $backendService->sendPushNotification($playerIds, $pushMessage);
//        $return["allresponses"] = $response;
//        $return = json_encode( $return);
//
//        print("\n\nJSON received:\n");
//        print($return);
//        print("\n");

        //TODO: send push notifications for messages older than 30 minutes with push flag ON and reset the flag
        $notifications = [];

        if (count($notifications)) {
            $this->line('Were sent '.count($notifications).' push notifications');
        } else {
            $this->line('No push notifications were sent');
        }
    }
}
