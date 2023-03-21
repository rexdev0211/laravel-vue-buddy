<?php

use App\EventMembership;
use App\Repositories\MessageRepository;
use App\UserPhoto;
use App\UserVideo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GroupMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $eventId = (int) env('EVENT_ID_SEED');
        $usersIds = EventMembership::where('event_id', $eventId)->get()->pluck('user_id');

        $photos = UserPhoto::all();
        $videos = UserVideo::all();

        $messageRepository = new MessageRepository();

        for ($i = 0; $i <= 10000; $i++) {
            $userFrom = $usersIds->random(1)->first();

            $msgType = Arr::random(['text', 'image', 'video', 'location']);
            $read = rand(0,1);

            $data = [
                'id' => $i,
                'user_from' => $userFrom,
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
