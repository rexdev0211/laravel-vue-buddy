<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEventRemovedBySender extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_messages', function (Blueprint $table) {
            $table
                ->boolean('is_removed_by_sender')
                ->after('deleted_for_user_to')
                ->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_messages', function (Blueprint $table) {
            $table->dropColumn('is_removed_by_sender');
        });
    }
}
