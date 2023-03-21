<?php

namespace App\Console\Commands;

use App\Message;
use App\Services\ChatService;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class CheckCacheConversationDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:check-conversations-duplicates 
                                                        {--chunk=}
                                                        {--regenerate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking duplicates in the conversations cache';

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
        $chunk = $this->option('chunk');
        $regenerate = $this->option('regenerate');

        if ($regenerate) {
            $this->info('The option for automatic regeneration of the cache chat is enabled ');
        }

        if (is_null($chunk)) {
            $this->info('No chunk parameter');
            return false;
        } else {
            $chunk = User::get()->chunk((int) $chunk);

            foreach ($chunk as $users) {
                foreach ($users as $user) {
                    $conversationsCache = json_decode(Redis::get('conversations:' . $user->id), true);
                    if (!is_null($conversationsCache)) {
                        $redisCacheDupes = collect($conversationsCache)->duplicates();

                        if (!$redisCacheDupes->isEmpty()) {
                            $this->info('Duplicate chats were found for the user - ' . $user->id);

                            if ($regenerate) {
                                $this->info('User cache regeneration is started');
                                Redis::del('conversations:' . $user->id);

                                $messagesCount = Message::where('user_to', $user->id)
                                    ->orWhere('user_from', $user->id)
                                    ->count();

                                if ($messagesCount !== 0) {
                                    $chatService = new ChatService();
                                    $chatService->setCurrentUser($user);
                                    $chatService->setPage(0);
                                    $chatService->setLimit($messagesCount);
                                    $conversations = $chatService->getConversationsForCache();
                                    $chatService->createConversationsCache($user->id, $conversations);

                                    $this->info('chats for user ' . $user->id . ' are cached. Total conversations - ' . count($conversations));
                                } else {
                                    $this->info('no active chats for this user');
                                }
                            }
                        }
                    }
                }
            }

            return true;
        }
    }
}