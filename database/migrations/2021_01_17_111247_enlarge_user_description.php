<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnlargeUserDescription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `users` CHANGE COLUMN `about` `about` VARCHAR(300);");

        /*Schema::table('users', function(Blueprint $table) {
            $table->string('about', 300)->change();
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `users` CHANGE COLUMN `about` `about` VARCHAR(150);");

        /*Schema::table('users', function(Blueprint $table) {
            $table->string('about', 150)->change();
        });*/
    }
}
