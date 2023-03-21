<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ManageUserPhotosTable20200404 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_photos', function($table) {
            $table->enum('manual_rating', ['unrated', 'clear', 'soft', 'adult', 'prohibited'])
                  ->default('unrated')
                  ->after('nudity_rating');

            $table->enum('status', ['queued', 'reviewed', 'challenged', 'overruled'])
                  ->default('queued')
                  ->after('manual_rating');

            $table->integer('reviewed_by')
                  ->default(null)
                  ->nullable()
                  ->after('status');

            $table->timestamp('reviewed_at')
                  ->default(null)
                  ->nullable()
                  ->after('reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_photos', function($table) {
            $table->dropColumn('manual_rating');
            $table->dropColumn('status');
            $table->dropColumn('reviewed_by');
            $table->dropColumn('reviewed_at');
        });
    }
}
