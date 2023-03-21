<?php

namespace App\Services;

use App\Event;
use App\Message;
use App\Repositories\MessageRepository;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use App\Models\Words\WordsFilter;

class SpamService
{
    const
        ACTION_GHOST = 'ghost',
        ACTION_SUSPEND = 'suspend',

        SCOPE_CHAT = 'chat',
        SCOPE_PROFILE = 'profile',
        SCOPE_REGISTRATION = 'registration',
        SCOPE_EVENT = 'event',

        REASON_MULTIREG = 'multireg',
        REASON_AFRICA = 'africa',
        REASON_10MSG = '10msg',
        REASON_BOUNCED = 'bounced',
        REASON_ADDRESS = 'address',
        REASON_CONTENT = 'content',
        REASON_IP = 'ip',
        REASON_REPORTED = 'reported',
        REASON_LIMITS = 'limits',
        REASON_SPAM_MAIL = 'spammail';

    /** @var User */
    protected $user;

    /** @var Event|null */
    protected $event;

    /** @var string|null */
    protected $content;

    /** @var string */
    protected $scope = self::SCOPE_CHAT;

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function userSuspendAttempt(): bool
    {
        if (!$this->rulesAreEnabled()) {
            return false;
        }

        $user = $this->getUser();
        $result = $this->shouldBeSuspended();
        if (!empty($result)) {
            $user->suspend();
            (new ChatService())->setMessageIsSuspendedOrActiveForRecipients($user, User::STATUS_SUSPENDED);
            $this->sendAdminEmail(self::ACTION_SUSPEND, $result);
        }
        return !empty($result);
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function userGhostAttempt(): bool
    {
        if (!$this->rulesAreEnabled()) {
            return false;
        }

        $user = $this->getUser();
        $result = $this->shouldBeGhosted();
        if (!empty($result)) {
            $user->ghost();
            $this->sendAdminEmail(self::ACTION_GHOST, $result);
            (new ChatService())->setGhostedOrActiveMessagesForRecipients($user, User::STATUS_GHOSTED);
        }
        return !empty($result);
    }

    /**
     * @return bool
     */
    protected function rulesAreEnabled(): bool
    {
        $user = $this->getUser();
        if (
            $user->user_group == User::GROUP_STAFF
            ||
            $user->isGhosted()
            ||
            $user->isSuspended()
            ||
            !config('const.ANTI_SPAM_ENABLED', true)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     */
    protected function shouldBeSuspended()
    {
        if ($this->scope == self::SCOPE_CHAT && $this->isIPBlocked()) {
            return self::REASON_IP;
        }

        if ($this->scope == self::SCOPE_PROFILE && $this->domainsBlocked()) {
            return self::REASON_CONTENT;
        }

        return false;
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     */
    protected function shouldBeGhosted()
    {
        $user = $this->getUser();

        if (in_array($this->scope, [self::SCOPE_CHAT, self::SCOPE_EVENT, self::SCOPE_PROFILE]) && $this->contentIsRestricted()) {
            $this->sendAdminEmail(SpamService::ACTION_GHOST, self::REASON_CONTENT);
            return self::REASON_CONTENT;
        }

        if ($this->scope == self::SCOPE_CHAT && $this->messageLimitsExceeded()) {
            $this->sendAdminEmail(SpamService::ACTION_GHOST, self::REASON_LIMITS);
            //return self::REASON_LIMITS;
        }

        // Only applies for accounts less than 24 hours old!
        $userAgeInHours = Carbon::parse($user->created_at)->diffInHours();
        if ($userAgeInHours < 24) {
            // For all new accounts where the registered email bounces,
            // ghost the user as soon as he writes the first message.
            // WE DON'T GHOST THEM ANYMORE
            if (
                $this->scope == self::SCOPE_CHAT
                &&
                $user->email_validation === 'bounce'
                &&
                !$user->isTrustedMessageSender()
            ) {
                $this->sendAdminEmail(SpamService::ACTION_GHOST, self::REASON_BOUNCED);
                //return self::REASON_BOUNCED;
            }

            // If an account writes the same message to 10 different people, then ghost the account.
            if (
                $this->scope == self::SCOPE_CHAT
                &&
                $this->tenOrMoreSimilarMessagesSent()
            ) {
                $this->sendAdminEmail(SpamService::ACTION_GHOST, self::REASON_10MSG);
                //return self::REASON_10MSG;
            }

            // Check whether user created multiple accounts or not.
            // He will be ghosted in case of positive result.
            if (
                $this->scope == self::SCOPE_REGISTRATION
                &&
                $this->multipleRegistrationsPerformed()
            ) {
                $this->sendAdminEmail(SpamService::ACTION_GHOST, self::REASON_MULTIREG);
                //return self::REASON_MULTIREG;
            }

            // Nigeria and Ghana
            if (
                $this->scope == self::SCOPE_REGISTRATION
                &&
                $this->ghostedIps()
            ) {
                $this->sendAdminEmail(SpamService::ACTION_GHOST, self::REASON_AFRICA);
                //return self::REASON_AFRICA;
            }
        }

        return false;
    }

    public function sendAdminEmail(string $action, string $reasonCode): void
    {
        $user = $this->getUser();

        if (Redis::keys("spamServiceAdminEmail-".$user->id)) {
            return;
        }
        Redis::set("spamServiceAdminEmail-".$user->id, null);

        $reason = trans("spam.$reasonCode.reason");
        $snippet = trans("spam.$reasonCode.snippet");
        $colors = [
            self::ACTION_GHOST => 'darkmagenta',
            self::ACTION_SUSPEND => 'darkred'
        ];

        $subject = "ID {$user->id} {$action}ed: $snippet";

        if ($reasonCode == self::REASON_BOUNCED) {
            $body = "Trigger warning for User <a href=\"https://buddy.net/admin/users?filterId={$user->id}\" target=\"_blank\">{$user->name} (#{$user->id})</a>";
        } else {
            $body =
                "User <a href=\"https://buddy.net/admin/users?filterId={$user->id}\" target=\"_blank\">{$user->name} (#{$user->id})</a> has been <span style=\"color: {$colors[$action]}\">{$action}ed</span>." .
                "<br/><br/>" .
                "<strong>Reason:</strong> $reason";

            $text = $this->getContentByReasonCode($reasonCode);
            if (!empty($text)) {
                $body .= "<br/><br/><h3>Details:</h3>$text";
            }
        }

        EmailService::sendMailStatic(
            config('const.ADMIN_EMAIL'),
            config('const.ADMIN_NAME'),
            $subject,
            $body
        );
    }

    public function getContentByReasonCode(string $reasonCode)
    {
        switch ($reasonCode) {
            case self::REASON_CONTENT:{
                $heading = '';
                $scope = $this->getScope();
                if ($scope == self::SCOPE_EVENT) {
                    $event = $this->getEvent();
                    if ($this->getEvent()) {
                        $heading = "<h4>Event #{$event->id} contains prohibited content:</h4>";
                    } else {
                        $heading = "<h4>Tried to create an event with a description:</h4>";
                    }
                }
                if ($scope == self::SCOPE_CHAT) {
                    $heading = "<h4>Prohibited message content:</h4>";
                }
                if ($scope == self::SCOPE_PROFILE) {
                    $heading = "<h4>Prohibited profile description:</h4>";
                }
                $content = $this->getContent();
                $messages = $this->getLast10MessagesPerType();
                return
                    $heading .
                    "<p>$content</p><br/><br/>" .
                    $messages;
            }
            case self::REASON_REPORTED:
            case self::REASON_BOUNCED:
            case self::REASON_10MSG:{
                return $this->getLast10MessagesPerType();
            }
            case self::REASON_MULTIREG:{
                return $this->getContent();
            }
            default:{
                return null;
            }
        }
    }

    public function getLast10MessagesPerType(): string
    {
        $user = $this->getUser();

        $messages = Message::where([
            'user_from' => $user->id,
            'msg_type' => Message::TYPE_TEXT,
            'channel' => Message::CHANNEL_USER
        ])
            ->orderByDesc('idate')
            ->limit(10)
            ->get()
            ->map(function($message){
                $msg = htmlentities($message->message);
                return "{$message->idate} to user #{$message->user_to}: <i>$msg</i>";
            })
            ->join("</br>\n");

        $eventMessages = Message::where([
            'user_from' => $user->id,
            'msg_type' => Message::TYPE_TEXT,
            'channel' => Message::CHANNEL_EVENT
        ])
            ->orderByDesc('idate')
            ->limit(10)
            ->get()
            ->map(function($message){
                $msg = htmlentities($message->message);
                return "{$message->idate} to user #{$message->user_to} (event #{$message->event_id}): <i>$msg</i>";
            })
            ->join("</br>\n");

        $groupMessages = Message::where([
            'user_from' => $user->id,
            'msg_type' => Message::TYPE_TEXT,
            'channel' => Message::CHANNEL_GROUP
        ])
            ->orderByDesc('idate')
            ->limit(10)
            ->get()
            ->map(function($message){
                $msg = htmlentities($message->message);
                return "{$message->idate} to event #{$message->event_id}: <i>$msg</i>";
            })
            ->join("</br>\n");

        $result =
            "<h4>Last 10 user messages:</h4>\n" .
            $messages . "</br></br>\n\n" .
            "<h4>Last 10 event messages:</h4>\n" .
            $eventMessages . "</br></br>\n\n" .
            "<h4>Last 10 group messages:</h4>\n" .
            $groupMessages;

        return $result;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function isIPBlocked(): bool
    {
        $user = $this->getUser();
        if (empty($user->ip)) {
            return false;
        }

        $blockedIPs = [];
        if (\Storage::exists('blockedIPs')) {
            $blockedIPs = preg_split("/[\r\n]+/", \Storage::get('blockedIPs'));
        }

        return in_array($user->ip, $blockedIPs);
    }

    /**
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function domainsBlocked(): bool
    {
        $chars = [',', '!', '?', '#'];
        $content = preg_replace('/[\x00-\x09\x0B-\x1F\x7F\xA0]/u', '', $this->getContent());

        if (!$content) {
            return false;
        }

        $blockedDomains = [];
        if (\Storage::exists('blockedKeywords')) {
            $blockedDomains = preg_split("/[\r\n]+/", \Storage::get('blockedKeywords'));
        }

        $contentArray = collect(explode(' ', str_replace($chars, ' ', $content)))
                        ->filter()
                        ->values()
                        ->all();

        return !empty(array_intersect($contentArray, $blockedDomains));
    }

    /**
     * @return bool
     */
    protected function messageLimitsExceeded(): bool
    {
        $user = $this->getUser();

        $count = Redis::get("msg-limit-24hrs:{$user->id}");
        if ($count === null) {
            /* Count messages to different members within last 24 hours */
            $count = Message::where('user_from', $user->id)
                ->where('idate', '>', new \DateTime('yesterday'))
                ->pluck('user_to')
                ->unique()
                ->count();

            Redis::setex("msg-limit-24hrs:{$user->id}", 3 * 3600, (int)$count);
        }

        if ((int)$count >= config('const.MESSAGES_SPAM_24_HOURS_LIMIT')) {
            return true;
        }

        $count = Redis::get("msg-limit-1hr:{$user->id}");
        if ((int)$count === null) {
            /* Count messages to different members within last 24 hours */
            $count = Message::where('user_from', $user->id)
                ->where('idate', '>', new \DateTime('-1 hours'))
                ->pluck('user_to')
                ->unique()
                ->count();

            Redis::setex("msg-limit-1hr:{$user->id}", 15 * 60, (int)$count);
        }

        if ($count >= config('const.MESSAGES_SPAM_1_HOUR_LIMIT')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function contentIsRestricted(): bool
    {
        $content = $this->getContent();
        if (empty($content)) {
            return false;
        }

        $restricted = $this->hasBlockedContent($content);
        if (!$restricted) {
            $asciiMessage = preg_replace('/[[:^print:]]/', "", $content);
            $restricted = $this->hasBlockedContent($asciiMessage);
        }
        return $restricted;
    }

    /**
     * @param string $content
     *
     * @return bool
     * @throws \Exception
     */
    protected function hasBlockedContent(string $content): bool
    {
        return $this->hasProhibitedWords($content);
    }

    /**
     * @param string $userMessage
     *
     * @return bool
     * @throws \Exception
     */
    protected function hasProhibitedWords(string $userMessage): bool
    {
        $userMessage = strtolower($userMessage);

        $blockedWords = WordsFilter::where('type', 'prohibited')->pluck('phrase');

        if ($blockedWords->count() > 0) {
            foreach ($blockedWords->toArray() as $phrase) {
                if (strpos($userMessage, strtolower(trim($phrase))) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *  Check whether user created multiple accounts or not.
     *  He will be ghosted in case of positive result.
     *
     * @return mixed
     */
    protected function multipleRegistrationsPerformed()
    {
        $user = $this->getUser();

        // if a second account is created from the same IP on the same day,
        // automatically ghost all accounts from that IP
        $sameIpDailyUsers = User::where('ip', $user->ip)
            ->whereBetween('created_at', [Carbon::yesterday(), Carbon::now()])
            ->get();

        $result = false;
        $content = '<h4>Blocked:</h4>';
        if ($sameIpDailyUsers->count() >= 5) {
            $result = true;
            foreach ($sameIpDailyUsers as $sameIpUser) {
                /** @var User $sameIpUser */
                $sameIpUser->ghost();
                $content .= "{$sameIpUser->name} #{$sameIpUser->id}, ip {$sameIpUser->ip}<br/>";
            }
        }
        $this->setContent($content);

        return $result;
    }

    /**
     * We have a huge problem with spam accounts being created from Nigeria and Ghana.
     * Automatically ghost (not suspend!) any accounts created from the following IP ranges:
     * 105.112.*
     * 197.210.*
     *
     * @return bool
     */
    protected function ghostedIps(): bool
    {
        $user = $this->getUser();
        return Str::startsWith($user->ip, ['105.112.', '197.210.', '102.89.']);
    }

    /**
     * Function checks if an account writes
     * the same message to 10 or more different people.
     *
     * @return bool
     */
    protected function tenOrMoreSimilarMessagesSent(): bool
    {
        $user = $this->getUser();

        // Get messages count
        $messagesCountTotal = Message::where('user_from', $user->id)->count();

        $sameImages                = 0;
        $sameVideos                = 0;
        $similarityPercentageArray = [];

        // If count >= 10
        if ($messagesCountTotal >= 9) {
            // hi! how are you?
            // Ignore messages shorter than 20 symbols
            $messages = Message::where('user_from', $user->id)->where(function($query) {
                $query->where('ml', '>=', 20)
                      ->orWhereNotNull('image_id')
                      ->orWhereNotNull('video_id');
            })->get()->toArray();

            $messagesCount = count($messages);

            if ($messagesCount < 9) {
                return false;
            }
           
            /* Scanning from the first message till the pre-last message */
            for ($i = 0; $i < ($messagesCount - 2); $i++) {
                $hasSamePhoto = false;
                $hasSameVideo = false;

                /* Scanning from the second message till the last message */
                for ($k = ($i + 1); $k < ($messagesCount - 1); $k++) {
                    /* Check image for same */
                    if (!empty($messages[$i]['image_id']) && !empty($messages[$k]['image_id']) && $messages[$i]['image_id'] == $messages[$k]['image_id']) {
                        $hasSamePhoto = true;
                    }

                    /* Check video for same  */
                    if (!empty($messages[$i]['video_id']) && !empty($messages[$k]['video_id']) && $messages[$i]['video_id'] == $messages[$k]['video_id']) {
                        $hasSameVideo = true;
                    }

                    /* Check message for same (only if it's not a video/photo message type) */
                    if (!$hasSamePhoto && !$hasSameVideo) {
                        $similarity = 0;
                        if (!empty($messages[$i]['message']) && !empty($messages[$k]['message'])) {
                            if (mb_strlen($messages[$i]['message']) >= 20 && mb_strlen($messages[$k]['message']) >= 20) {
                                similar_text($messages[$i]['message'], $messages[$k]['message'], $similarity);
                            }
                        }

                        // Add similarity value to accumulative array
                        $similarityPercentageArray[] = $similarity;
                    }
                }

                if ($hasSamePhoto) {
                    $sameImages++;
                }

                if ($hasSameVideo) {
                    $sameVideos++;
                }
            }

            /* If user has 10 ot more same images */
            if ($sameImages >= 10 || $sameVideos >= 10 || collect($similarityPercentageArray)->avg() >= 90) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return Event|null
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * @param Event|null $event
     */
    public function setEvent(?Event $event): void
    {
        $this->event = $event;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function testSpamService(): array
    {
        $user = $this->getUser();
        $result = [
            'suspended'           => $this->shouldBeSuspended(),
            'ghosted'             => $this->shouldBeGhosted(),
            'ip_blocked'          => $this->isIPBlocked(),
            'message_limit'       => $this->messageLimitsExceeded(),
            '10_or_more_similar'  => $this->tenOrMoreSimilarMessagesSent(),
            'ghosted_ips'         => $this->ghostedIps(),
            'multiple_reg'        => $this->multipleRegistrationsPerformed(),
            'email_bounced'       => $user->isTrustedMessageSender(),
        ];

        return $result;
    }

    /**
     * Replace restricted words to special word.
     *
     * @param $text
     * @return string
     */
    public function replaceRestrictedWords($text): string
    {
        $text = (string) $text;
        $words = explode(" ", $text);
        $blockedWords = WordsFilter::where('type', 'restricted')->pluck('phrase');

        if ($blockedWords->count() > 0) {
            foreach ($blockedWords->toArray() as $phrase) {
                $phrase = strtolower($phrase);
                foreach ($words as $key => $word) {
                    if (stripos($word, $phrase) !== false) {
                        $words[$key] = preg_replace('/\b'.$phrase.'\b/', '', strtolower($word));
                    }
                }
            }

            $text = implode(" ", $words);
        }

        return $text;
    }
}
