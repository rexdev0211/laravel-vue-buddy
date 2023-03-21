<?php

use App\Message;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ManageMessagesTable20191209 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function($table) {
            $table->boolean('is_read_notification_checked')
                  ->default(0)
                  ->after('is_read');
        });

        Message::whereIsRead('yes')->update([
            'is_read_notification_checked' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function($table) {
            $table->dropColumn('is_read_notification_checked');
        });
    }
}
