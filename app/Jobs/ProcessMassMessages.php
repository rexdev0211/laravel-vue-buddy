<?php

namespace App\Jobs;

use App\Events\NewMessageReceived;
use App\Message;
use App\Models\Message\InternalMessagesQueue;
use App\Repositories\MessageRepository;
use App\Services\ChatService;
use App\User;
use App\Jobs\RefreshConversationsJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;


class ProcessMassMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $queueId;
    protected $message;
    protected $targetUsersIds;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The maximum number of exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($queueId, $message, $targetUsersIds)
    {
        $this->queueId = $queueId;
        $this->message = $message;
        $this->targetUsersIds = $targetUsersIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $queue = InternalMessagesQueue::where('id', $this->queueId)->first();
        $messageRepository = new MessageRepository();

        foreach ($this->targetUsersIds as $usersId) {
            $data = [
                'user_to' => $usersId,
                'user_from' => $this->message['sender_id'],
                'message' => $this->message['text'],
                'msg_type' => Message::TYPE_TEXT,
                'channel' => Message::CHANNEL_USER,
                'is_bulk' => 1
            ];

            $message = $messageRepository->createMessage($data);
            $interlocutor = User::find($this->message['sender_id']);
            $recipient = User::find($message['user_to'])->first();

            $conversation = (new MessageRepository())->getConversationAndClearCache($interlocutor, $recipient, $message, Message::CHANNEL_USER, null, null);

            event(new NewMessageReceived($conversation));
        }

        $queue = InternalMessagesQueue::where('id', $this->queueId)->first();

        $queue->processed = $queue->processed + count($this->targetUsersIds);
        if ($queue->processed >= $queue->messages) {
            $queue->is_finished = true;
        }
        $queue->save();
    }
}
