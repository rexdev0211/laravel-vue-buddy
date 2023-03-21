<?php

namespace App\Console\Commands;

use App\Message;
use App\Services\ChatService;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class CreateConversationsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:conversations
                            { --userId= : User Id} 
                            { --chunk= : Chunk}
                            { --force : Ignoring cache on all users }
                            { --order= : Orderxs }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Filling the cache for all users or for one user';

    /**
     * @var ChatService
     */
    protected $chatService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ChatService $chatService)
    {
        parent::__construct();
        $this->chatService = $chatService;
    }

    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $chunk = (int) $this->option('chunk');
        $userId = $this->option('userId');
        $force = (bool) $this->option('force');

        try {
            if ($userId) {
                $this->createCacheForUser((int) $userId);
            } else if (!$userId && $chunk) {
                $this->createCacheForAllUsers($chunk, $force);
            }
        } catch (\Exception $e) {
            $this->showMsg($e->getMessage(), 'error');
            return false;
        }

        return true;
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function createCacheForUser(int $userId): bool
    {
        $this->showMsg('The cache filling for User ' . $userId . ' has started');
        $checkHash = Redis::get('conversations:' . $userId);

        if (!is_null($checkHash)) {
            Redis::del('conversations:' . $userId);
        }

        $user = User::where('id', $userId)->first();

        $messagesCount = Message::where('user_to', $userId)
                                ->orWhere('user_from', $userId)
                                ->count();

        if ($messagesCount !== 0) {
            $chatService = $this->chatService;
            $chatService->setCurrentUser($user);
            $chatService->setPage(0);
            $chatService->setLimit($messagesCount);
            $conversations = $chatService->getConversationsForCache();
            $chatService->createConversationsCache($userId, $conversations);

            $this->showMsg('chats for user ' . $userId . ' are cached. Total conversations - ' . count($conversations), 'info');
        } else {
            $this->showMsg('no active chats for this user', 'info');
        }

        return true;
    }

    /**
     * @param int $chunk
     * @param bool $force
     * @return bool
     */
    public function createCacheForAllUsers(int $chunk, bool $force): bool
    {
        $chatService = $this->chatService;
        $chunk = User::orderBy('id', $this->option('order') == 'desc' ? 'desc' : 'asc')->get()->chunk($chunk);

        foreach ($chunk as $users) {
            foreach ($users as $user) {
                $userId = $user->id;
                $checkHash = Redis::get('conversations:' . $userId);

                if ($force && !is_null($checkHash)) {
                    Redis::del('conversations:' . $userId);
                } else if (!$force && !is_null($checkHash)) {
                    continue;
                }

                $messagesCount = Message::where('user_to', $userId)
                    ->orWhere('user_from', $userId)
                    ->count();

                if ($messagesCount !== 0) {
                    $chatService->setCurrentUser($user);
                    $chatService->setPage(0);
                    $chatService->setLimit($messagesCount);
                    $conversations = $chatService->getConversationsForCache();
                    $chatService->createConversationsCache($userId, $conversations);

                    $this->showMsg('chats for user ' . $userId . ' are cached. Total conversations - ' . count($conversations), 'info');
                } else {
                    $this->showMsg('no chats found for user ' . $userId);
                }
            }
        }

        $this->showMsg('Chats are cached for all users', 'info');
        return true;
    }

    /**
     * @param $msg
     * @param null $type
     */
    private function showMsg($msg, $type = null)
    {
        switch ($type) {
            case 'warning':
                $this->warn(Carbon::now()->format('Y-m-d H:i:s').' - '.$msg);
                break;

            case 'error':
                $this->error(Carbon::now()->format('Y-m-d H:i:s').' - '.$msg);
                break;

            default:
                $this->info(Carbon::now()->format('Y-m-d H:i:s').' - '.$msg);
        }
    }
}