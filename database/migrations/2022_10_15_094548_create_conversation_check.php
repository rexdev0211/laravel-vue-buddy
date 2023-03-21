<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationCheck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (false === Schema::hasColumn('users', 'last_conversation_check')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dateTime('last_conversation_check')->nullable();
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
        if (true === Schema::hasColumn('users', 'last_conversation_check')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('last_conversation_check');
            });
        }
    }
}
