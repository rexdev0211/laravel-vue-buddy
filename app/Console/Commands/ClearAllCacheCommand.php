<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ClearAllCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:all-clear { --key= }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all cache';

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
     */
    public function handle()
    {
        $key = $this->option('key');

        if (!empty($key)) {
            $redisKeys = Redis::keys($key);
        } else {
            $redisKeys = Redis::keys('*');
        }

        if (empty($redisKeys)) {
            $this->info('No data cache to delete');
            return true;
        }

        $bar = $this->output->createProgressBar(count($redisKeys));

        try {
            foreach ($redisKeys as $redisKey) {
                Redis::del($redisKey);
                $bar->advance();
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return false;
        }

        $this->info('Cache removed for all users');
        $bar->finish();
        return true;
    }
}