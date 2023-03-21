<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewUserNotificationsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_notifications')->default(0)->after('user_group');
            $table->boolean('has_new_notifications')->default(0)->after('user_group');
            $table->boolean('has_new_visitors')->default(0)->after('user_group');
            $table->boolean('has_new_messages')->default(0)->after('user_group');
        });

        DB::table('users')->where('has_unseen_notif_general', 'yes')->update(['has_notifications' => 1]);
        DB::table('users')->where('has_unseen_notif_general', 'no')->update(['has_notifications' => 0]);

        DB::table('users')->where('has_unseen_notif_taps', 'yes')->update(['has_new_notifications' => 1]);
        DB::table('users')->where('has_unseen_notif_taps', 'no')->update(['has_new_notifications' => 0]);

        DB::table('users')->where('has_unseen_notif_visitors', 'yes')->update(['has_new_visitors' => 1]);
        DB::table('users')->where('has_unseen_notif_visitors', 'no')->update(['has_new_visitors' => 0]);

        DB::table('users')->where('has_unseen_notices', 'yes')->update(['has_new_messages' => 1]);
        DB::table('users')->where('has_unseen_notices', 'no')->update(['has_new_messages' => 0]);

        /*Schema::table('users', function (Blueprint $table) {
            // has_new_taps alias
            $table->dropColumn('has_unseen_notif_taps');
            // has_new_visitors alias
            $table->dropColumn('has_unseen_notif_visitors');
            // has_new_messages alias
            $table->dropColumn('has_unseen_notices');
            // has_new_taps || has_new_visitors
            $table->dropColumn('has_unseen_notif_general');
        });*/

        if (Schema::hasColumn('users', 'has_unseen_user_messages')){
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('has_unseen_user_messages');
            });
        }

        if (Schema::hasColumn('users', 'has_unseen_event_messages_bak')){
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('has_unseen_event_messages_bak');
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
        /*Schema::table('users', function (Blueprint $table) {
            // has_new_taps alias
            $table->enum('has_unseen_notif_taps', ['yes', 'no'])->default('no');
            // has_new_visitors alias
            $table->enum('has_unseen_notif_visitors', ['yes', 'no'])->default('no');
            // has_new_messages alias
            $table->enum('has_unseen_notices', ['yes', 'no'])->default('no');
            // has_new_taps || has_new_visitors
            $table->enum('has_unseen_notif_general', ['yes', 'no'])->default('no');
        });*/

        /*DB::table('users')->where('has_new_taps', 1)->update(['has_unseen_notif_taps' => 'yes']);
        DB::table('users')->where('has_new_taps', 0)->update(['has_unseen_notif_taps' => 'no']);

        DB::table('users')->where('has_new_visitors', 1)->update(['has_unseen_notif_visitors' => 'yes']);
        DB::table('users')->where('has_new_visitors', 0)->update(['has_unseen_notif_visitors' => 'no']);

        DB::table('users')->where('has_new_messages', 1)->update(['has_unseen_notices' => 'yes']);
        DB::table('users')->where('has_new_messages', 0)->update(['has_unseen_notices' => 'no']);

        DB::table('users')
            ->where('has_new_taps', 1)
            ->orWhere('has_new_visitors', 1)
            ->update(['has_unseen_notif_general' => 'yes']);

        DB::table('users')
            ->where(['has_new_taps' => 0, 'has_new_visitors' => 0])
            ->update(['has_unseen_notif_general' => 'no']);*/

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('has_notifications');
            $table->dropColumn('has_new_notifications');
            $table->dropColumn('has_new_visitors');
            $table->dropColumn('has_new_messages');
        });
    }
}
