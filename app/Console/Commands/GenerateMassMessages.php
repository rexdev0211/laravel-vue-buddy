<?php

namespace App\Console\Commands;

use App\Event;
use App\EventMembership;
use App\Message;
use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use App\Services\ChatService;
use App\User;
use Faker\Factory;
use Illuminate\Console\Command;

class GenerateMassMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:mass_messages
                                                  {--count=}
                                                  {--from=}
                                                  {--to=}
                                                  {--channel=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate mass messages command';

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
        $env = config('app.env');

        if ($env == 'production') {
            $this->warn('this script is not for production');
            return 0;
        }

        $from = $this->option('from');
        $to = $this->option('to');
        $countLimit = $this->option('count');

        if (empty($countLimit)) {
            $this->warn('count is required');
            return 0;
        }

        if (!empty($from)) {
            $this->info('from user should be `'.$from.'`');
        }

        if (!empty($to)) {
            $this->info('user to should be '.$to);
        }

        for($i=1; $i<=$countLimit; $i++) {
            /** @var User $userFrom */
            $userFrom = User::query(null);

            if (!empty($from)) {
                $userFrom = $userFrom->where(function($q) use($from) {
                    $q->where('name', $from)
                        ->orWhere('email', $from);
                });
            }

            /** @var User $userFrom */
            $userFrom = $userFrom->inRandomOrder()->first();

            if (empty($userFrom)) {
                $this->warn('user from not found');
                return 0;
            }

            /** @var User $userTo */
            $userTo = empty($to)
                ? User::where('id', '!=', $userFrom->id)->inRandomOrder()->limit(1)->first()
                : User::where('id', '!=', $userFrom->id)->where(function($q) use($to) {
                    $q->where('name', $to)
                        ->orWhere('email', $to);
                    })->first();

            if (null === $userTo) {
                $this->warn('user to is null ... break.');
                break;
            }

            /*
             * SEND MESSAGE
             */

            $userRepository = new UserRepository();
            $messageRepository = new MessageRepository();

            $faker = Factory::create();

            $channels = [
                Message::CHANNEL_USER,
                Message::CHANNEL_GROUP,
            ];

            $channel = $this->option('channel');

            if (empty($channel)) {
                $channel = $channels[rand(0,count($channels)-1)];
            }

            $data = [
                'user_from' => $userFrom->id,
                'message' => $faker->text(rand(100,300)),
                'msg_type' => Message::TYPE_TEXT,
                'channel' => $channel,
                'is_sender_ghosted' => $userFrom->isGhosted() ? 1 : 0
            ];
            if ($channel != Message::CHANNEL_GROUP) {
                $data['user_to'] = $userTo->id;
                $data['is_recipient_ghosted'] = $userTo->isGhosted() ? 1 : 0;
            }

            $event = null;

            if ($channel != Message::CHANNEL_USER) {
                /** @var Event $event */
                $event = Event::where('status', Event::STATUS_ACTIVE)
                    ->whereIn('type', [
                        Event::TYPE_BANG,
                        // other events throw errors, about members list.
                    ])
                    ->inRandomOrder()
                    ->first();

                if (null === $event) {
                    $this->warn('random event not found');
                    continue;
                }

                if (!empty($userFrom)) {
                    $memberUserFrom = $event->members()
                        ->where('user_id', $userFrom->id)
                        ->count();

                    if ($memberUserFrom <= 0) {
                        try {
                            EventMembership::insert([
                                'event_id' => $event->id,
                                'user_id' => $userFrom->id,
                                'status' => 'host',
                            ]);
                        } catch (\Exception $e) {
                            $this->warn($e->getMessage());
                        }
                    }
                }

                if (!empty($userTo)) {
                    $memberUserTo = $event->members()
                        ->where('user_id', $userTo->id)
                        ->count();

                    if ($memberUserTo <= 0) {
                        try {
                            EventMembership::insert([
                                'event_id' => $event->id,
                                'user_id' => $userTo->id,
                                'status' => 'member',
                            ]);
                        } catch (\Exception $e) {
                            $this->warn($e->getMessage());
                        }
                    }
                }

                $event = $event->refresh();

                $eventId = $event->id;
                $data['event_id'] = $eventId;
            }

            $message = $messageRepository->createMessage($data);

            $this->info('CHANNEL: '.$channel);

            if (null !== $event && !isset($message['event']['members'])) {
                $this->warn('skip message, event do not have members');
                continue;
            }

            $this->info('sent message from '.$userFrom->email.' to '.$userTo->email);

            /*
             * CLEAR CACHE
             */
            (new MessageRepository())->getConversationAndClearCache($userTo, $userFrom, $message, $channel, $event, $eventId);
        }

        return 0;
    }
}
