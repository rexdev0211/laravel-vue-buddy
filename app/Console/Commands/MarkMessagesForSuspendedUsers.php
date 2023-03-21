<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MarkMessagesForSuspendedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:mark_suspended';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark all messages for suspended users as Suspended';

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
        $this->info('[Messages:MarkSuspended] Started.');
        /* Get suspended users */
        $users = \App\User::whereIn('status', ['suspended', 'ghosted'])->pluck('id');
        $total = count($users);

        if ($total > 0) {
            $processed = 0;
            $this->info('[Messages:MarkSuspended] '.count($users).' Suspended users found.');
            $chunks = collect($users)->chunk(10000);
            /* Set messages as suspended */
            foreach ($chunks as $chunk) {
                $processed += count($chunk);
                \App\Message::whereIn('user_from', $chunk->toArray())->update([
                    'is_sender_suspended' => 1,
                ]);
                $this->info('[Messages:MarkSuspended] '.round($processed/($total/100), 2).'% suspended users processed.');
            }
        } else {
            $this->info('[Messages:MarkSuspended] No suspended users found.');
        }
    }
}
