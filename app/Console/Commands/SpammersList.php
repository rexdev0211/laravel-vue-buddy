<?php

namespace App\Console\Commands;

use App\Repositories\UserRepository;
use App\Services\EmailService;
use Illuminate\Console\Command;

class SpammersList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:spammers_list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification when spammers list changes';

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

    private function arrayEvolved($oldList, $newList) {
        return count(array_diff($newList, $oldList));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cacheKey = 'prev_spammers_list';
        $oldList = \Cache::get($cacheKey, []);

        //TODO: maybe we should just verify users who sent more than X email in the last hour
        $users = $this->userRepository->getProbablySpammers();
        $newList = $users->sortBy('id')->pluck('id')->toArray();

        \Cache::put($cacheKey, $newList, 150 * 60);

        if ($this->arrayEvolved($oldList, $newList)) {
            $body = "Spammers list on BB has changed from ".print_r($oldList, true)." to ".print_r($newList, true).". Please verify.";
            $this->emailService->sendMail(config('const.ADMIN_EMAIL'), config('const.ADMIN_NAME'), 'Spammers list on BB has changed', $body);

            $message = 'Spam list has been changed and admin notified';
            $this->line($message);
            \Log::info($message);
        } else {
            $message = 'Spam list is the same';
            $this->line($message);
            \Log::info($message);
        }
    }
}
