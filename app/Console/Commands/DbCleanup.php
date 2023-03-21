<?php

namespace App\Console\Commands;

use App\Repositories\EventRepository;
use App\Repositories\MessageRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\UserVisitRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DbCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:db_cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old DB data';

    private $notificationRepository;
    private $eventRepository;
    private $messageRepository;
    private $userVisitRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NotificationRepository $notificationRepository, EventRepository $eventRepository,
                                MessageRepository $messageRepository, UserVisitRepository $userVisitRepository)
    {
        $this->notificationRepository = $notificationRepository;
        $this->eventRepository = $eventRepository;
        $this->messageRepository = $messageRepository;
        $this->userVisitRepository = $userVisitRepository;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //notifications
        $notificationsDate = Carbon::today()->subDays(7);
        $notificationsCount = $this->notificationRepository->deleteOldNotifications($notificationsDate);

        $message = 'Deleted '.$notificationsCount.' notifications';
        $this->line($message);
        \Log::info($message);

        //events
        // $eventsDate = Carbon::today()->subDays(7);
        // $eventsCount = $this->eventRepository->deleteOldEvents($eventsDate);

        // $message = 'Deleted '.$eventsCount.' events';
        // $this->line($message);
        // \Log::info($message);

        //messages
        // $messagesDate = Carbon::today()->subDays(90);
        // $messagesCount = $this->messageRepository
        //     ->where('msg_type', 'text')
        //     ->where('idate', '<', $messagesDate)
        //     ->delete();
        //
        // $message = 'Deleted '.$messagesCount.' messages';
        // $this->line($message);
        // \Log::info($message);

        //visits
        $visitsDate = Carbon::today()->subDays(7);
        $visitsCount = $this->userVisitRepository
            ->where('idate', '<', $visitsDate)
            ->delete();

        $message = 'Deleted '.$visitsCount.' visits';
        $this->line($message);
        \Log::info($message);
    }
}
