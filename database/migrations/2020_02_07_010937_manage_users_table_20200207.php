<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ManageUsersTable20200207 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ip')
                  ->nullable();

            $table->string('fingerprint')
                  ->nullable();

            $table->string('honeypot')
                  ->nullable();

            $table->enum('map_type', ['manual', 'automatic', 'none'])
                  ->default('none');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('fingerprint');
            $table->dropColumn('honeypot');
            $table->dropColumn('map_type');
        });
    }
}
