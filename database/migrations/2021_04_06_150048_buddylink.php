<?php

use Illuminate\Database\Migrations\Migration;

class Buddylink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE users RENAME COLUMN buddy_name TO link");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE users RENAME COLUMN link TO buddy_name");
    }
}
