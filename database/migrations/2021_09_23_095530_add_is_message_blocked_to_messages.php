<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMessageBlockedToMessages extends Migration
{
    protected $connection = 'mongodb';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)
              ->table('messages', function (Blueprint $table) {
                    $table->index('is_blocked_by_sender');
                    $table->index('is_blocked_by_recipient');
              });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)
              ->table('messages', function (Blueprint $table) {
                    $table->dropIndex('is_blocked_by_sender_1');
                    $table->dropIndex('is_blocked_by_recipient_1');
              });
    }
}
