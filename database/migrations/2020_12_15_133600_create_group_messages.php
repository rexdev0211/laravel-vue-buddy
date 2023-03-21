<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\GroupMessage;

class CreateGroupMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_messages', function (Blueprint $table) {
            $table->id();
            $table->integer('user_from');
            $table->integer('event_id');
            $table->text('message');
            $table->enum('msg_type', [
                GroupMessage::TYPE_TEXT,
                GroupMessage::TYPE_VIDEO,
                GroupMessage::TYPE_IMAGE,
                GroupMessage::TYPE_LOCATION,
                GroupMessage::TYPE_JOINED,
                GroupMessage::TYPE_LEFT,
            ]);
            $table->integer('image_id')->nullable();
            $table->integer('video_id')->nullable();
            $table->boolean('deleted')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamp('idate')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_messages');
    }
}
