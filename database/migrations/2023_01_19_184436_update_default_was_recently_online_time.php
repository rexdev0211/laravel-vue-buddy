<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDefaultWasRecentlyOnlineTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('countries')->where('id', '<>', 266)->update(['was_recently_online_time' => config('const.USER_WAS_RECENTLY_MINUTES') * 60]);
        DB::table('countries')->where('id', 266)->update(['was_recently_online_time' => 72 * 60 * 60]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('countries')->where('id', '<>', 266)->update(['was_recently_online_time' => 900]);
        DB::table('countries')->where('id', 266)->update(['was_recently_online_time' => 72 * 60 * 60]);
    }
}
