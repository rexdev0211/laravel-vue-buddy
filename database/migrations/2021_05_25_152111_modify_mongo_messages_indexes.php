<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyMongoMessagesIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')
              ->table('messages', function (Blueprint $table) {
                  $table->index('event_id');
                  $table->index(['event_id', 'channel']);
              });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')
              ->table('messages', function (Blueprint $table) {
                  $table->dropIndex('event_id_1');
                  $table->dropIndex(['event_id', 'channel']);
              });
    }
}
