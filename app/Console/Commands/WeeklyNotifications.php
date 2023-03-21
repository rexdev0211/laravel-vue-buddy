<?php

namespace App\Console\Commands;

use App\Repositories\MessageRepository;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class WeeklyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:weekly_notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly reminder of unread messages';

    private $messageRepository;
    private $emailService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MessageRepository $messageRepository, EmailService $emailService)
    {
        $this->messageRepository = $messageRepository;
        $this->emailService = $emailService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $carbon = Carbon::now();
        $currentDay = strtolower($carbon->format('l'));

        $entries = $this->messageRepository->getUsersWithUnreadMessages('weekly', $currentDay);

        foreach ($entries as $entry) {
            $user = $entry['user'];
            $this->emailService->sendIntervalNotificationsStatistics(
                $user->email,
                $user->name,
                $entry['unreadMessagesCount'],
                $user->language,
                'weekly_reminders'
            );
        }

        if (count($entries)) {
            $message = 'Were sent '.count($entries).' weekly reminders';
            $this->line($message);
            \Log::info($message);
            return 0;
        }

        $message = 'No weekly reminders were sent';
        $this->line($message);
        \Log::info($message);
    }
}
