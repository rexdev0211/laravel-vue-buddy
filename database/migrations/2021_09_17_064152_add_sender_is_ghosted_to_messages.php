<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSenderIsGhostedToMessages extends Migration
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
              ->table('messages', function (Blueprint $table) {
                    $table->index('is_sender_ghosted');
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
                    $table->dropIndex('is_sender_ghosted_1');
              });
    }
}
