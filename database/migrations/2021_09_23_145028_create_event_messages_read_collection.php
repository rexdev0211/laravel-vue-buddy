<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventMessagesReadCollection extends Migration
{
    /**
     * @var string
     */
    protected $connection = 'mongodb';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)
              ->create('event_messages_read', function (Blueprint $table) {
                  $table->integer('user_id');
                  $table->index('user_id');
                  $table->integer('event_id');
                  $table->index('event_id');
                  $table->dateTime('latest_read');
              });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('event_messages_read');
    }
}
