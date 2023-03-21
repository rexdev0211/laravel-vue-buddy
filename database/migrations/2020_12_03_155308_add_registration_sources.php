<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegistrationSources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `users` CHANGE `registered_via` `registered_via` ENUM('web', 'app', 'net', 'ios', 'android') NOT NULL DEFAULT 'web';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `users` CHANGE `registered_via` `registered_via` ENUM('web', 'app') NOT NULL DEFAULT 'web';");
    }
}
