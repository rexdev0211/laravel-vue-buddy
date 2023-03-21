<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCountryIdFromUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (true === Schema::hasColumn('users', 'country_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('country_id');
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
        if (false === Schema::hasColumn('users', 'country_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('country_id')
                    ->nullable()
                    ->after('lng');
            });
        }
    }
}
