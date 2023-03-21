<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserVideosStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `user_videos` CHANGE `status` `status` ENUM('waiting', 'processing', 'processed', 'accessible') NOT NULL DEFAULT 'waiting';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `user_videos` CHANGE `status` `status` ENUM('waiting', 'processing', 'processed') NOT NULL DEFAULT 'waiting';");
    }
}
