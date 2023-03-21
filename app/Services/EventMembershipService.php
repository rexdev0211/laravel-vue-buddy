<?php


namespace App\Services;

use App\Event;
use App\EventMembership;
use App\Events\EventMembershipUpdated;
use App\Events\NewGroupMessageReceived;
use App\Message;
use App\Repositories\MessageRepository;
use App\User;

class EventMembershipService
{
    /** @var string */
    protected $action;

    /** @var Event */
    protected $event;

    /** @var int */
    protected $eventId;

    /** @var User|null */
    protected $user;

    /** @var EventMembership|null */
    protected $membership;

    /** @var User */
    protected $currentUser;

    /** @var EventMembership|null */
    protected $currentUserMembership;

    /**
     * @throws \Exception
     */
    public function updateStatus(): void
    {
        switch ($this->action) {
            case EventMembership::ACTION_REQUEST: {
                $this->performRequestAction();
                break;
            }
            case EventMembership::ACTION_ACCEPT: {
                $this->performAcceptAction();
                // Send a 'Joined chat' message to the event channel
                $this->sendMessageToGroupChat(Message::TYPE_JOINED);
                break;
            }
            case EventMembership::ACTION_REJECT: {
                $this->performRejectAction();
                break;
            }
            case EventMembership::ACTION_REMOVE: {
                $this->performRemoveAction();
                // Send a 'Left chat' message to the event channel
                $this->sendMessageToGroupChat(Message::TYPE_LEFT);

                $userId = $this->getUser()->id;
                (new ChatService())->removeCacheConversation(Message::CHANNEL_GROUP, $userId, null, $this->eventId);

                break;
            }
            case EventMembership::ACTION_LEAVE: {
                $this->performLeaveAction();
                // Send a 'Left chat' message to the event channel
                $this->sendMessageToGroupChat(Message::TYPE_LEFT);

                $currentUserId = $this->getCurrentUser()->id;
                (new ChatService())->removeCacheConversation(Message::CHANNEL_GROUP, $currentUserId, null, $this->eventId);

                break;
            }
        }
    }

    protected function performRequestAction(): void
    {
        if (empty($this->currentUserMembership)) {
            $newMembership = new EventMembership();
            $newMembership->event_id = $this->event->id;
            $newMembership->user_id = $this->currentUser->id;
            $newMembership->status = EventMembership::STATUS_REQUESTED;
            $newMembership->save();
        } else {
            $this->currentUserMembership->status = EventMembership::STATUS_REQUESTED;
            $this->currentUserMembership->save();
        }

        // To host
        // Add a new membership request marker
        // Update event members count
        // Update event members array if visible
        $broadcastingEvent = new EventMembershipUpdated([
            'recipient_id' => $this->event->user_id,
            'event_id' => $this->event->id,
            'event' => [
                Event::ATTRIBUTES_MODE_GENERAL => [],
                Event::ATTRIBUTES_MODE_DISCOVER => [],
                Event::ATTRIBUTES_MODE_FULL => [
                    'id' => $this->event->id,
                    'members' => $this->event->getMembersList($this->event->user)
                ],
            ],
            'action' => EventMembership::ACTION_REQUEST
        ]);

        event($broadcastingEvent);
    }

    protected function performAcceptAction(): void
    {
        if (!empty($this->membership)) {
            $this->membership->status = EventMembership::STATUS_MEMBER;
            $this->membership->save();
        } else {
            throw new \Exception('Membership entry not found', 422);
        }

        $members = $this->event->getMembersList();

        // To everybody
        // Update event members count
        // Update event members array if visible
        $broadcastingEvent = new EventMembershipUpdated([
            'ignore_recipient_id' => [
                // Ignore host
                $this->event->user_id,
                // Ignore accepted user
                $this->membership->user_id,
            ],
            'event_id' => $this->event->id,
            'event' => [
                Event::ATTRIBUTES_MODE_GENERAL => [],
                Event::ATTRIBUTES_MODE_DISCOVER => [
                    'id' => $this->event->id,
                    'members_count' => $this->event->activeMembersCount,
                ],
                Event::ATTRIBUTES_MODE_FULL => [
                    'id' => $this->event->id,
                    'members_count' => $this->event->activeMembersCount,
                    'members' => $members
                ],
            ],
            'action' => EventMembership::ACTION_ACCEPT
        ]);

        event($broadcastingEvent);

        $eventGeneralData = $this->event->getAttributesByMode(Event::ATTRIBUTES_MODE_GENERAL, $this->user);
        // Need to highlight a new event in the "my events" list
        $eventGeneralData['is_new'] = true;

        // To recipient
        // Update event membership status
        $broadcastingEvent = new EventMembershipUpdated([
            'recipient_id' => $this->membership->user_id,
            'event_id' => $this->event->id,
            'event' => [
                // Need general data to add event to the "myEvents"-list
                Event::ATTRIBUTES_MODE_GENERAL => $eventGeneralData,
                Event::ATTRIBUTES_MODE_DISCOVER => [
                    'id' => $this->event->id,
                    'membership' => EventMembership::STATUS_MEMBER,
                    'members_count' => $this->event->activeMembersCount,
                ],
                Event::ATTRIBUTES_MODE_FULL => [
                    'id' => $this->event->id,
                    'membership' => EventMembership::STATUS_MEMBER,
                    'members_count' => $this->event->activeMembersCount,
                    'members' => $members
                ],
            ],
            'action' => EventMembership::ACTION_ACCEPT
        ]);

        event($broadcastingEvent);
    }

    protected function performRejectAction(): void
    {
        if (!empty($this->membership)) {
            $this->membership->status = EventMembership::STATUS_REJECTED;
            $this->membership->save();
        } else {
            throw new \Exception('Membership entry not found', 422);
        }

        // To recipient
        // Update event membership status
        $broadcastingEvent = new EventMembershipUpdated([
            'recipient_id' => $this->membership->user_id,
            'event_id' => $this->event->id,
            'event' => [
                Event::ATTRIBUTES_MODE_GENERAL => [],
                Event::ATTRIBUTES_MODE_DISCOVER => [
                    'id' => $this->event->id,
                    'membership' => EventMembership::STATUS_REJECTED,
                ],
                Event::ATTRIBUTES_MODE_FULL => [
                    'id' => $this->event->id,
                    'membership' => EventMembership::STATUS_REJECTED,
                ],
            ],
            'action' => EventMembership::ACTION_REJECT
        ]);

        event($broadcastingEvent);
    }

    protected function performRemoveAction(): void
    {
        if (!empty($this->membership)) {
            $this->membership->status = EventMembership::STATUS_REMOVED;
            $this->membership->save();
        } else {
            throw new \Exception('Membership entry not found', 422);
        }

        $members = $this->event->getMembersList();

        // To everybody
        // Update event members count
        // Update event members array if visible
        $broadcastingEvent = new EventMembershipUpdated([
            'ignore_recipient_id' => [
                $this->event->user_id,
                $this->membership->user_id,
            ],
            'event_id' => $this->event->id,
            'event' => [
                Event::ATTRIBUTES_MODE_GENERAL => [],
                Event::ATTRIBUTES_MODE_DISCOVER => [
                    'id' => $this->event->id,
                    'members_count' => $this->event->activeMembersCount,
                ],
                Event::ATTRIBUTES_MODE_FULL => [
                    'id' => $this->event->id,
                    'members_count' => $this->event->activeMembersCount,
                    'members' => $members
                ],
            ],
            'action' => EventMembership::ACTION_REMOVE
        ]);

        event($broadcastingEvent);

        // To recipient
        // Update event membership status
        $broadcastingEvent = new EventMembershipUpdated([
            'recipient_id' => $this->membership->user_id,
            'event_id' => $this->event->id,
            'event' => [
                Event::ATTRIBUTES_MODE_GENERAL => [],
                Event::ATTRIBUTES_MODE_DISCOVER => [
                    'id' => $this->event->id,
                    'membership' => EventMembership::STATUS_REMOVED,
                    'members_count' => $this->event->activeMembersCount,
                ],
                Event::ATTRIBUTES_MODE_FULL => [
                    'id' => $this->event->id,
                    'membership' => EventMembership::STATUS_REMOVED,
                    'members_count' => $this->event->activeMembersCount,
                    'members' => $members
                ],
            ],
            'action' => EventMembership::ACTION_REMOVE
        ]);

        event($broadcastingEvent);
    }

    protected function performLeaveAction(): void
    {
        $this->currentUserMembership->status = EventMembership::STATUS_LEAVED;
        $this->currentUserMembership->save();

        // To everybody except host
        // Update event members count
        // Update event members array if visible
        $broadcastingEvent = new EventMembershipUpdated([
            'ignore_recipient_id' => [
                $this->event->user_id,
            ],
            'event_id' => $this->event->id,
            'event' => [
                Event::ATTRIBUTES_MODE_GENERAL => [],
                Event::ATTRIBUTES_MODE_DISCOVER => [
                    'id' => $this->event->id,
                    'members_count' => $this->event->activeMembersCount,
                ],
                Event::ATTRIBUTES_MODE_FULL => [
                    'id' => $this->event->id,
                    'members_count' => $this->event->activeMembersCount,
                    'members' => $this->event->getMembersList()
                ],
            ],
            'action' => EventMembership::ACTION_LEAVE
        ]);

        event($broadcastingEvent);

        // To host
        $broadcastingEvent = new EventMembershipUpdated([
            'recipient_id' => $this->event->user_id,
            'event_id' => $this->event->id,
            'event' => [
                Event::ATTRIBUTES_MODE_GENERAL => [],
                Event::ATTRIBUTES_MODE_DISCOVER => [
                    'id' => $this->event->id,
                    'members_count' => $this->event->activeMembersCount,
                ],
                Event::ATTRIBUTES_MODE_FULL => [
                    'id' => $this->event->id,
                    'members_count' => $this->event->activeMembersCount,
                    'members' => $this->event->getMembersList($this->event->user)
                ],
            ],
            'action' => EventMembership::ACTION_LEAVE
        ]);

        event($broadcastingEvent);
    }

    protected function sendMessageToGroupChat(string $messageType): void
    {
        // Message data
        $data = [
            'user_from' => $this->user->id ?? $this->currentUser->id,
            'event_id' => $this->event->id,
            'msg_type' => $messageType,
            'channel' => Message::CHANNEL_GROUP,
        ];

        // Create a message
        /** @var Message $message */
        $message = (new MessageRepository)->createMessage($data);

        $conversationBroadcasted = ChatService::getGroupConversationGeneralAttributes(
            $this->user,
            $this->event,
            $message,
            0
        );
        event(new NewGroupMessageReceived($conversationBroadcasted));
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    /** @param int $id */
    public function setEventId(int $id): void
    {
        $this->eventId = $id;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return EventMembership|null
     */
    public function getMembership(): ?EventMembership
    {
        return $this->membership;
    }

    /**
     * @param EventMembership|null $membership
     */
    public function setMembership(?EventMembership $membership): void
    {
        $this->membership = $membership;
    }

    /**
     * @return User
     */
    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    /**
     * @param User $currentUser
     */
    public function setCurrentUser(User $currentUser): void
    {
        $this->currentUser = $currentUser;
    }

    /**
     * @return EventMembership|null
     */
    public function getCurrentUserMembership(): ?EventMembership
    {
        return $this->currentUserMembership;
    }

    /**
     * @param EventMembership|null $currentUserMembership
     */
    public function setCurrentUserMembership(?EventMembership $currentUserMembership): void
    {
        $this->currentUserMembership = $currentUserMembership;
    }
}