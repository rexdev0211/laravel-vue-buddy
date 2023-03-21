<?php

namespace App\Jobs;

use App\Message;
use App\Repositories\MessageRepository;
use App\Services\ChatService;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class CheckConversationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        /** @var User user */
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!empty($this->user->last_conversation_check)) {
            $lastConversationCheck = Carbon::parse($this->user->last_conversation_check);

            if ($lastConversationCheck->diffInHours() < env('CONVERSATION_CHECK_INTERVAL', 12)) {
                return;
            }
        }

        $this->user->last_conversation_check = now()->toDateTimeString();
        $this->user->save();

        $cachedConversations = (new MessageRepository())->getAllCachedConversations($this->user);

        if (empty($cachedConversations)) {
            return;
        }

        $dbConversations = (new MessageRepository())->getConversationMessagesAll($this->user->id, 999999, 0, $this->user);

        $need = count($dbConversations) != count($cachedConversations);

        if (true === $need) {
            // del
            Redis::del('conversations:' . $this->user->id);

            // create
            $this->createCacheForUser($this->user);
        }
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
        }
    }
}
