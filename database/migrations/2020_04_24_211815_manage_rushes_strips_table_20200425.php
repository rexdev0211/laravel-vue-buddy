<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ManageRushesStripsTable20200425 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rushes_strips', function (Blueprint $table) {
            $table->boolean('profile_attached')
                  ->defaut(true)
                  ->after('video_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rushes_strips', function (Blueprint $table) {
            $table->dropColumn('profile_attached');
        });
    }
}
