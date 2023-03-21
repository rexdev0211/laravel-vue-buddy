<?php

namespace App\Console\Commands;

use App\Repositories\MessageRepository;
use App\Services\EmailService;
use Illuminate\Console\Command;

class DailyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:daily_notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily reminder of unread messages';

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
        $entries = $this->messageRepository->getUsersWithUnreadMessages('daily');
        foreach ($entries as $entry) {
            $user = $entry['user'];
            $this->emailService->sendIntervalNotificationsStatistics(
                $user->email,
                $user->name,
                $entry['unreadMessagesCount'],
                $user->language,
                'daily_reminders'
            );
        }

        if (count($entries)) {
            $message = 'Were sent '.count($entries).' daily reminders';
            $this->line($message);
            \Log::info($message);
        } else {
            $message = 'No daily reminders were sent';
            $this->line($message);
            \Log::info($message);
        }
    }
}
