<?php

namespace App\Console\Commands;

use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RemoveSuspendedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:remove-suspended-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove suspended users';

    private $userRepository;
    private $messageRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository, MessageRepository $messageRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->messageRepository = $messageRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $maxDate = Carbon::now()->subDays(21)->format('Y-m-d');
        $builder = $this->userRepository->where('status', 'suspended')->where('last_active', '<', $maxDate)->select('id');
        $userIds = $builder->get()->pluck('id')->toArray();
        $fromCnt = $this->messageRepository->whereIn('user_from', $userIds)->delete();
        $toCnt = $this->messageRepository->whereIn('user_to', $userIds)->delete();
        $usersCnt = $builder->whereIn('id', $userIds)->delete();

        $message = "Deleted ".$usersCnt." suspended users and ".($fromCnt+$toCnt)." their messages";
        $this->line($message);
        \Log::info($message);
    }
}
