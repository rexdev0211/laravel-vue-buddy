<?php

namespace App\Console\Commands;

use App\Message;
use App\User;
use Illuminate\Console\Command;

class SetGhostedUsersToMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:mark_ghosted { --chunk= }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark all messages for ghosted users as ghosted';

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
            $this->ghosted((int) $this->option('chunk'));
        }
    }

    /**
     * @param int $chunk
     */
    protected function flagReset(int $chunk)
    {
        $count = 0;
        Message::where('is_sender_ghosted', 1)
                ->orWhere('is_recipient_ghosted', 1)
                ->chunkById($chunk, function ($messages) use (&$count){
                    $count += count($messages);
                    $this->info('Flags dropped - ' . $count);
                    foreach ($messages as $message) {
                        $message->update([
                            'is_sender_ghosted' => 0,
                            'is_recipient_ghosted' => 0,
                            'is_sender_suspended' => 0
                        ]);
                    }
                });
    }

    /**
     * @param int $chunk
     */
    protected function ghosted(int $chunk)
    {
        $this->info('Started...');

        $counter = 0;

        User::select('id')
            ->where('status', User::STATUS_GHOSTED)
            ->chunk($chunk, function ($users) use (&$counter){
                $counter += count($users);
                $this->info('Received users - ' . $counter);

                foreach ($users->toArray() as $user) {
                    $userId = $user['id'];

                    Message::where(function ($query) use ($userId) {
                        $query->where('user_from', $userId)
                            ->update(['is_sender_ghosted' => 1]);
                    })->orWhere(function ($query) use ($userId) {
                        $query->where('user_to', $userId)
                            ->update(['is_recipient_ghosted' => 1]);
                    });
                }
            });

        $this->info('Finish...');
    }
}
