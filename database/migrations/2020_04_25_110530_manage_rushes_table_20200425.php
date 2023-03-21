<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ManageRushesTable20200425 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rushes', function (Blueprint $table) {
            $table->enum('status', ['active', 'suspended'])
                  ->index()
                  ->defaut('active')
                  ->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rushes', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
