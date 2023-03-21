<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use App\Services\BuddyLinkService;

class AssignBuddyLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:assign_buddy_link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign buddy name for every active user';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        $logPath = storage_path('logs/buddyLink.log');
        $users = User::whereNull('link')
            ->where('status', 'active')
            ->limit(2000)
            ->get();

        foreach($users as $index => $user) {
            /** @var User $user */
            $forceRandom = false;
            do {
                $user->link = BuddyLinkService::getComputedBuddyLink($user->name, $forceRandom, true);
                try {
                    $user->save();
                } catch (\Exception $e) {
                    $forceRandom = true;

                    $line = date('d-m-Y H:i:s') . " #$index >>> duplicate >>> {$user->name} > {$user->link}";
                    $this->error($line);
                    file_put_contents($logPath, " $line\n", FILE_APPEND);
                }
            } while ($user->isDirty());

            $line = date('d-m-Y H:i:s') . " #$index {$user->name} > {$user->link}";
            $this->line($line);
            file_put_contents($logPath, "$line\n", FILE_APPEND);
        }
    }
}
