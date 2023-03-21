<?php

use App\EventMembership;
use App\User;
use Illuminate\Database\Seeder;

class EventMembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $eventId = (int) env('EVENT_ID_SEED');
        $eventMembers = EventMembership::select('user_id')->where('event_id', $eventId)->get()->pluck('user_id')->toArray();
        $usersIds = User::select('id')->whereNotIn('id', $eventMembers)->take(400)->get()->pluck('id')->toArray();

        $data = [];

        foreach ($usersIds as $userId) {
            $data[] = [
                'event_id' => $eventId,
                'user_id' => $userId,
                'status' => EventMembership::STATUS_MEMBER
            ];
        }

        EventMembership::insert($data);
    }
}
