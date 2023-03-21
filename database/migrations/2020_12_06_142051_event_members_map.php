<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\EventMembership;

class EventMembersMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_members_map', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->integer('user_id');
            $table->enum('status', [
                EventMembership::STATUS_HOST,
                EventMembership::STATUS_REQUESTED,
                EventMembership::STATUS_REJECTED,
                EventMembership::STATUS_MEMBER,
                EventMembership::STATUS_LEAVED,
                EventMembership::STATUS_REMOVED,
            ])->default(EventMembership::STATUS_REQUESTED);

            $table->index('event_id');
            $table->index(['event_id', 'status']);
            $table->unique(['event_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_members_map');
    }
}
