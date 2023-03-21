<?php

namespace App\Console\Commands;

use App\Message;
use App\Services\ChatService;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RebuildConversationsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rebuild:conversations
                            { --userId= : User Id} 
                            { --chunk= : Chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the cache for all users or for one user';

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
        $chunk  = (int) $this->option('chunk');
        $userId = $this->option('userId');

        try {
            if ($userId) {
                $this->createCacheForUser((int) $userId);
            } else if (!$userId && $chunk) {
                $this->createCacheForAllUsers($chunk);
            }
        } catch (\Exception $e) {
            $this->showMsg($e->getMessage(), 'error');
            return false;
        }

        return true;
    }

    /**
     * @param int $userId
     * @return void
     */
    public function createCacheForUser(int $userId): void
    {
        $this->showMsg('The cache filling for User ' . $userId . ' has started');
        $conversations = json_decode(Redis::get('conversations:' . $userId), true);
        $this->chatService->updateConversationsCache($userId, $conversations);
    }

    /**
     * @param int $chunk
     * @return bool
     */
    public function createCacheForAllUsers(int $chunk): void
    {
        $chunk = User::orderBy('id', 'ASC')->get()->chunk($chunk);

        foreach ($chunk as $users) {
            foreach ($users as $user) {
                $conversations = json_decode(Redis::get('conversations:' . $user->id), true);
                if (count($conversations ?: [])) {
                    $this->chatService->updateConversationsCache($user->id, $conversations);
                    $this->showMsg('chats for user ' . $user->id . ' are cached. Total conversations - ' . count($conversations ?: []), 'info');
                } else {
                    $this->showMsg('no chats found for user ' . $user->id);
                }
            }
        }

        $this->showMsg('Chats are cached for all users', 'info');
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