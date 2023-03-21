<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::inRandomOrder()
                      ->limit(500)
                      ->get();

        foreach ($users as $user) {
            $data = [
                'user_id' => $user->id,
                'title' => Str::random(5),
                'description' => Str::random(100),
                'event_date' => Carbon::now()->addDay(rand(0, 1000))->format('Y-m-d'),
                'time' => '17:25',
                'locality' => 'Berlin',
                'state' => 'Berlin',
                'country' => 'Germany',
                'country_code' => 'DE',
                'address'  => 'Berlin, Germany',
                'lat' => '52.09469506',
                'lng' => 12.68646240,
                'category' => 'sex',
                'type' => 'friends',
                'chemsfriendly' => rand(0, 1),
                'address_type' => 'full_address',
                'is_profile_linked' => rand(0, 1),
                'is_sticky' => 0,
                'created_at' => Carbon::now()->subDays(7),
                'gps_geom' => DB::raw("(ST_GeomFromText('POINT(12.68646240 52.09469506)'))")
            ];

            (new \App\Repositories\EventRepository())->createEvent($data);

        }
    }
}
