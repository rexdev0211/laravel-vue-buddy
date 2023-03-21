<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternalMessagesQueue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internal_messages_queue', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('messages');
            $table->integer('processed')
                  ->default(0);
            $table->boolean('is_finished')
                  ->default(0)
                  ->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internal_messages_queue');
    }
}
