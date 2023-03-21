<?php

namespace App\Console\Commands;

use App\Message;
use App\Repositories\MessageRepository;
use App\Services\ChatService;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class CheckConversationCountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:conversation_counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check how many messages users has in cache and in DB. If there is different we\'re re-compiling messages cache.';

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
        /** @var User $users */
        $users = User::whereNull('deleted_at')
            ->where(function($q) {
                $q->where('last_conversation_check', '<=', now()->subHours(env('CONVERSATION_CHECK_INTERVAL', 12))->toDateTimeString())
                    ->orWhereNull('last_conversation_check');
            })
            ->get();

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        $deletedCount = 0;

        foreach ($users as $user) {
            $bar->advance();

            if (!empty($userlast_conversation_check)) {
                $lastConversationCheck = Carbon::parse($user->last_conversation_check);

                if ($lastConversationCheck->diffInHours() < env('CONVERSATION_CHECK_INTERVAL', 12)) {
                    continue;
                }
            }

            $user->last_conversation_check = now()->toDateTimeString();
            $user->save();

            $cachedConversations = (new MessageRepository())->getAllCachedConversations($user);

            if (empty($cachedConversations)) {
                continue;
            }

            $dbConversations = (new MessageRepository())->getConversationMessagesAll($user->id, 999999, 0, $user);

            $need = count($dbConversations) != count($cachedConversations);

            if (true === $need) {
//                $this->warn(' '.$user->email.' '.count($dbConversations).'/'.count($cachedConversations));

                // del
                Redis::del('conversations:' . $user->id);

                // create
                $this->createCacheForUser($user);

                // counter
                $deletedCount++;
            }
        }

        $bar->finish();

        $this->info('total users: '.count($users));
        $this->info('cache re-compiled for '.$deletedCount.' users');

        return 0;
    }

    /**
     * @param User $user
     */
    public function createCacheForUser(User $user): void
    {
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

//            $this->info('--->> '.count($conversations));
        }

//        $this->line('====');
    }
}
