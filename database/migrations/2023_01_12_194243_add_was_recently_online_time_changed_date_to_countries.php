<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AddWasRecentlyOnlineTimeChangedDateToCountries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->integer('was_recently_online_time')->default(900);
            $table->date('changed_date')->default(Carbon::today());
        });

        DB::table('countries')->where('id', 266)->update(['was_recently_online_time' => 72 * 60 * 60]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('was_recently_online_time');
            $table->dropColumn('changed_date');
        });
    }
}
