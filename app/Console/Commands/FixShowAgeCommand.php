<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixShowAgeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:show_age';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix show age column in users table. This command changing value of broken show_age data from empty row to value NO.';

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
        $updated = \Illuminate\Support\Facades\DB::table('users')
            ->whereNull('show_age')
            ->orWhere('show_age', "")
            ->orWhere('show_age', 0)
            ->orWhere('show_age', false)
            ->update([
                'show_age' => 'no',
            ]);

        $this->info('updated '.$updated);

        return 0;
    }
}
