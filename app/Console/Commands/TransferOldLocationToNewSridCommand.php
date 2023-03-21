<?php

namespace App\Console\Commands;

use App\Event;
use App\User;
use Illuminate\Console\Command;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Grimzy\LaravelMysqlSpatial\Types\LineString;

class TransferOldLocationToNewSridCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transfer:old_location_to_new_srid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer old coordinate system into new special SRID';

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
        /** @var User $user */
        $user = User::all();

        $bar = $this->output->createProgressBar(count($user));

        /** @var User $user */
        foreach ($user as $user) {
            $point = new Point($user->lat, $user->lng, 4326); // (lat, lng)
            $user->location = mb_convert_encoding($point, 'UTF-8');
            $user->save();

            $bar->advance();
        }

        $bar->finish();

        // ============

        /** @var Event $events */
        $events = Event::all();

        $bar = $this->output->createProgressBar(count($events));

        /** @var Event $event */
        foreach ($events as $event) {
            $point = new Point($event->lat, $event->lng, 4326); // (lat, lng)
            $event->location_geom = mb_convert_encoding($point, 'UTF-8');;
            $event->save();

            $bar->advance();
        }

        $bar->finish();

        return 0;
    }
}
