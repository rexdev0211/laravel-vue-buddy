<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ManageEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->enum('category', ['fun', 'sex'])
                  ->index()
                  ->default('sex')
                  ->after('likes');

            $table->boolean('chemsfriendly')
                  ->default(0)
                  ->after('category');

            $table->dropColumn('event_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->dropColumn('chemsfriendly');

            $table->enum('event_type', ['public', 'private'])
                  ->index()
                  ->default('private')
                  ->after('likes');
        });
    }
}
