<?php

namespace App\Jobs;

use App\Message;
use App\Services\ChatService;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class CreateConversationsCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $userId;

    public $timeout = 120; // 2 minutes

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $user = User::where('id', $this->userId)->first();

        $messagesCount = Message::where('user_to', $this->userId)
                                ->orWhere('user_from', $this->userId)
                                ->count();

        $checkHash = Redis::get('conversations:' . $this->userId);

        if ($messagesCount !== 0) {
            $chatService = new ChatService();
            $chatService->setCurrentUser($user);
            $chatService->setPage(0);
            $chatService->setLimit($messagesCount);
            $conversations = $chatService->getConversationsForCache();

            if (!is_null($checkHash)) {
                Redis::del('conversations:' . $this->userId);
            }

            $chatService->createConversationsCache($this->userId, $conversations);
        }

        return true;
    }
}
