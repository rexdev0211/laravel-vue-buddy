<?php

namespace App\Console\Commands;

use App\Repositories\UserRepository;
use App\Services\EmailService;
use Illuminate\Console\Command;

class MonthlyLoginReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:monthly_login_reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly login reminder when there are no messages';

    private $userRepository;
    private $emailService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository, EmailService $emailService)
    {
        $this->userRepository = $userRepository;
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
        $users = $this->userRepository->getUsersWithoutMessagesInLastMonth();

        foreach ($users as $user) {
            $this->emailService->sendIntervalNotificationsStatistics($user->email, $user->name, '', $user->language, 'monthly_login_reminder');
        }

        $userIds = $users->pluck('id')->toArray();

        if (count($userIds)) {
            $this->userRepository->updateMonthlyReminder($userIds);

            $message = 'Were sent '.count($userIds).' monthly login reminders';
            $this->line($message);
            \Log::info($message);
        } else {
            $message = 'No monthly login reminders were sent';
            $this->line($message);
            \Log::info($message);
        }
    }
}
