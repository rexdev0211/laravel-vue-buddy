<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanOauthRefreshTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:oauth_refresh_tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old oauth refresh tokens';

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
        $count = \DB::table('oauth_refresh_tokens')
            ->where('expires_at', '<=', now()->subMonths(1)->toDateTimeString())
            ->delete();

        $this->info($count.' deleted');

        return 0;
    }
}
