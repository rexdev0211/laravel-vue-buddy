<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanOldEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:old_emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old emails';

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
     * @return int
     */
    public function handle()
    {
        \DB::table('sent_emails')
            ->where('created_at', '<=', now()->subHours(48)->toDateTime())
            ->delete();

        $this->info('old emails removed');

        return 0;
    }
}
