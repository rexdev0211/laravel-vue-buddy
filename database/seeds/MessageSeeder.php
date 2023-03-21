<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\UserPhoto;
use App\UserVideo;
use App\User;
use App\Event;

use App\Repositories\MessageRepository;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $events = Event::all();
        $photos = UserPhoto::all();
        $videos = UserVideo::all();

        $force103633 = random_int(1,10) == 10;
        $force103627 = random_int(1,10) == 10;
        $noEvent = random_int(1,10) > 8;

        $messageRepository = new MessageRepository();
        for ($i = 0; $i < 200000; $i++) {
            $userFrom = $force103633 ? 103633 : $users->random()->id;
            $userTo = $force103627 ? 103627 : $users->random()->id;
            while ($userFrom == $userTo) {
                $userTo = $users->random()->id;
            }
            $eventId = $noEvent ? null : $events->random()->id;
            $msgType = Arr::random(['text', 'image', 'video', 'location']);
            $read = rand(0,1);

            $data = [
                'id' => $i,
                'user_from' => $userFrom,
                'user_to' => $userTo,
                'event_id' => $eventId,
                'message' => Str::random(),
                'msg_type' => $msgType,
                'channel' => Arr::random(['user', 'event', 'group']),
                'is_read' => $read ? 'yes' : 'no',
                'is_read_cloak' => (int)$read,
                'is_sender_suspended' => rand(0,1),
                'is_removed_by_sender' => rand(0,1),
                'is_bulk' => 0,
                'idate' => new DateTime(),
                'deleted' => rand(0,1),
            ];
            if ($data['msg_type'] == 'image') {
                $data['image_id'] = $photos->random()->id;
            }
            if ($data['msg_type'] == 'video') {
                $data['video_id'] = $videos->random()->id;
            }
            $messageRepository->createMessage($data);
            echo "#$i\n";
        }
    }
}
