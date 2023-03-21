<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Message;
use App\User;

class MessagesAddBulkField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function($table) {
            $table->boolean('is_bulk')
                ->default(0);
        });

        $messages = \DB::select('
            select
                user_from,
                message,
                count(*) as count
            from messages
            where
                # от админа
                user_from in (select id from users where user_group = \'staff\')
            group by
                user_from,
                message
            having count > 10
        ');

        $messages = collect($messages)
            ->pluck('message')
            ->all();

        $staffIds = User::where('user_group', 'staff')
            ->select('id')
            ->pluck('id')
            ->all();

        Message::whereIn('user_from', $staffIds)
            ->whereIn('message', $messages)
            ->update(['is_bulk' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function($table) {
            $table->dropColumn('is_bulk');
        });
    }
}
