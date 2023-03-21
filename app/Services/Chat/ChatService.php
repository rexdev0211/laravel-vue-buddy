<?php

namespace App\Services;

use App\User;
use DateTime;
use App\Message;
use Illuminate\Support\Facades\Redis;

class ChatService
{
    /**
     * Get conversation hash
     *
     * @param string   $channel     Channel
     * @param ?integer $senderId    Sender Id
     * @param ?integer $recepientId Recepient Id
     * @param ?integer $eventId     Event Id
     * 
     * @return string
     */
    private function getConversationHash(
        string $channel,
        ?int $senderId = null,
        ?int $recipientId = null,
        ?int $eventId = null
    ): string {
        switch ($channel) {
            case Message::CHANNEL_USER:
                $sender    = max($senderId, $recipientId);
                $recipient = min($senderId, $recipientId);
                $hash      = md5("$channel:$sender:$recipient");
                break;

            case Message::CHANNEL_EVENT:
                $sender    = max($senderId, $recipientId);
                $recipient = min($senderId, $recipientId);
                $hash      = md5("$channel:$sender:$recipient:$eventId");
                break;

            case Message::CHANNEL_GROUP:
                $hash = md5("$channel:$eventId");
                break;

            default:
                throw new \Exception('Unknown message channel', 500);
                break;
        }

        return $hash;
    }
    
    /**
     * Get user conversations
     *
     * @param User $user User
     *
     * @return void
     */
    public function getConversations(User $user): array
    {
        return [];
    }

    /**
     * Get dialogs
     *
     * @param string $dialog Dialog hash
     *
     * @return array
     */
    public function getDialogs(string $dialog): array
    {
        return [];
    }

    /**
     * Send message
     *
     * @param User  $user User
     * @param array $data Message data
     * 
     * @return array
     */
    public function sendMessage(
        User $user,
        array $data
    ): array {
        if ($data['msg_type'] == Message::TYPE_TEXT && (empty($data['message']) && $data['message'] !== '0')) {
            throw new \Exception('Message content was not set');
        }

        if ($data['msg_type'] == Message::TYPE_VIDEO && empty($data['video_id'])) {
            throw new \Exception('Message video_id was not set');
        }

        if ($data['msg_type'] == Message::TYPE_IMAGE && empty($data['image_id'])) {
            throw new \Exception('Message image_id was not set');
        }

        if ($data['message'] && !$data['is_bulk']) {
            $data['ml'] = strlen($data['message']);
        }

        $data['is_read']       = 'no';
        $data['is_read_cloak'] = 0;
        $data['is_bulk']       = $data['is_bulk'] ?? 0;
        $data['idate']         = new DateTime();

        $hash = $this->getConversationHash(
            $data['channel'],
            $data['user_from'],
            $data['user_to'] ?? null,
            $data['event_id'] ?? null
        );

        $message = $this->model->create($data);

        $this->incrementDialogCount($hash);
        $this->updateUserConversations($user, $hash, $message);

        return $message;
    }

    /**
     * Get user conversations
     *
     * @param User $user User
     * 
     * @return array
     */
    private function getUserConversations(
        User $user
    ): array {
        return Redis::hGetAll('user:' . $user->id . ':conversations');
    }

    /**
     * Update user conversations
     *
     * @param User   $user    User
     * @param string $hash    Hash
     * @param array  $message Message
     * 
     * @return boolean
     */
    private function updateUserConversations(
        User $user,
        string $hash,
        array $message
    ): bool {
        return Redis::hSet(
            'user:' . $user->id . ':conversations',
            $hash,
            $message
        );
    }

    /**
     * Increment dialog counter
     *
     * @return boolean
     */
    private function incrementDialogCount(string $hash): bool
    {
        return Redis::incr('dialog:' . $hash . ':total', 1);
    }

    /**
     * Remove message
     *
     * @return boolean
     */
    public function removeMessage(string $hash, int $messageId): bool
    {
        return false;
    }
}
