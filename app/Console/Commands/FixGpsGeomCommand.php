<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class FixGpsGeomCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:gps-geom {--chunk= : Chunk}
                                         {--debug= : Debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fixing gps geom for users';

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
        if ($this->option('chunk')) {
            $this->fixGpsGeom((int) $this->option('chunk'));
        } else {
            $this->info('No chunk parameter');
        }
    }

    /**
     * @param int $chunk
     */
    public function fixGpsGeom(int $chunk)
    {
        $chunks = User::selectRaw("id, lat, lng, gps_geom, point(lng, lat) AS 'gps'")
                      ->havingRaw('gps != gps_geom')
                      ->get()
                      ->chunk($chunk);
        foreach ($chunks as $users) {
                $usersArray = $users->toArray();
                foreach ($usersArray as $user) {
                    User::where('id', $user['id'])
                        ->update(['gps_geom' => $user['gps']]);

                    if ($this->option('debug')) {
                        $this->info('User #'.$user['id'].' lat: '.$user['lat'].' lng: '.$user['lng']);
                    }
                }
        }
    }
}
