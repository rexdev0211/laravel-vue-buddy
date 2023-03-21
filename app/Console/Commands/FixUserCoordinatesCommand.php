<?php

namespace App\Console\Commands;

use App\User;
use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixUserCoordinatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:user_coordinates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix user coordinates command';

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
        /** @var User $users */
        $users = User::select(DB::raw('*, ST_x(gps_geom) as x_point, ST_y(gps_geom) as y_point'))
            ->where(function($query) {
                $query->where(DB::raw('ST_x(gps_geom)'), '>=', User::MAX_ALLOWED_LONGITUDE_X)
                    ->orWhere(DB::raw('ST_x(gps_geom)'), '<=', User::MIN_ALLOWED_LONGITUDE_X);
            })->orWhere(function($query) {
                $query->where(DB::raw('ST_y(gps_geom)'), '>=', User::MAX_ALLOWED_LATITUDE_Y)
                    ->orWhere(DB::raw('ST_y(gps_geom)'), '<=', User::MIN_ALLOWED_LATITUDE_Y);
            })->get();

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $user) {
            $user->lng = 0;
            $user->lat = 0;
            $user->save();
            $bar->advance();
        }

        $bar->finish();

        return 0;
    }
}
