<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ClearConversationsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:conversations-clear { --userId= : User ID to delete the cache of conversations of this user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deleting conversations cache for one user or all users';

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
        $userId = $this->option('userId');
        if (is_null($userId)) {
            $this->cacheDeleteForUsers();
        } else {
            $this->cacheDeleteForUser((int) $userId);
        }
    }

    /**
     * Deleting the cache for a specific user
     * @param int $userId
     * @return bool
     */
    protected function cacheDeleteForUser(int $userId): bool
    {
        Redis::del('conversations:' . $userId);
        $this->info(`Cache deleted for user {$userId}`);
        return true;
    }

    /**
     * Deleting the conversation cache of all users
     * @return bool
     */
    protected function cacheDeleteForUsers(): bool
    {
        $redisKeys = Redis::keys('conversations:*');

        if (empty($redisKeys)) {
            $this->info('No conversation cache to delete');
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

        $this->info('Chat cache removed for all users');
        $bar->finish();
        return true;
    }
}