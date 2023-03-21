<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPercentageFieldToUserVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (false === Schema::hasColumn('user_videos', 'percentage')) {
            Schema::table('user_videos', function (Blueprint $table) {
                $table->integer('percentage')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (true === Schema::hasColumn('user_videos', 'percentage')) {
            Schema::table('user_videos', function (Blueprint $table) {
                $table->dropColumn('percentage');
            });
        }
    }
}
