<?php

namespace App\Console\Commands;

use App\Message;
use App\UserBlocked;
use Illuminate\Console\Command;

class SetBlockedUsersToMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:mark_blocked { --chunk= }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return false|void
     */
    public function handle()
    {
        if (!$this->option('chunk')) {
            $this->info('No chunk parameter');
            return false;
        }

        if ($this->confirm('Do you wish to continue?')) {
            $this->flagReset((int) $this->option('chunk'));
            $this->blocked((int) $this->option('chunk'));
        }
    }

    /**
     * @param int $chunk
     */
    public function flagReset(int $chunk)
    {
        $count = 0;
        Message::where('is_blocked_by_sender', 1)
               ->orWhere('is_blocked_by_recipient', 1)
               ->chunkById($chunk, function ($messages) use (&$count){
                   $count += count($messages);
                   $this->info('Flags dropped - ' . $count);
                   foreach ($messages as $message) {
                       $message->update([
                           'is_blocked_by_sender' => 0,
                           'is_blocked_by_recipient' => 0
                       ]);
                   }
               });
    }

    /**
     * @param int $chunk
     */
    public function blocked(int $chunk)
    {
        $this->info('Started...');

        $userBlockedCounter = 0;

        UserBlocked::select('user_id', 'user_blocked_id')
                   ->orderBy('user_id')
                   ->chunk($chunk, function ($usersBlocked) use (&$userBlockedCounter) {
                       $this->info('Received users - ' . ($userBlockedCounter + count($usersBlocked)));

                       foreach ($usersBlocked->toArray() as $userBlocked) {
                           $initiatorId = $userBlocked['user_id'];
                           $suppressedId = $userBlocked['user_blocked_id'];

                           Message::where(function ($query) use ($initiatorId, $suppressedId) {
                               $query->where('user_from', $initiatorId)
                                   ->where('user_to', $suppressedId)
                                   ->update(['is_blocked_by_recipient' => 1]);
                           })->orWhere(function ($query) use ($initiatorId, $suppressedId) {
                               $query->where('user_to', $initiatorId)
                                   ->where('user_from', $suppressedId)
                                   ->update(['is_blocked_by_sender' => 1]);
                           });
                       }
                   });

        $this->info('Finish...');
    }
}
