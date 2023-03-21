<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class SetStaffUsersDefaultDiscreetMode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:staff-users-default-discreet-mode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set discreet mode on for all staff users';

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
     * @return mixed
     */
    public function handle()
    {
        $users = User::whereUserGroup(User::GROUP_STAFF)->get();
        foreach ($users as $user) {
            $user->discreet_mode = true;
            $user->invisible = true;
            $user->save();
        }

        $message = "Discreet mode activated for ".$users->count()." staff members.";
        $this->line($message);
        \Log::info($message);
    }
}
