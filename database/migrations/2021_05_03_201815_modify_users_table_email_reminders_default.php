<?php

use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUsersTableEmailRemindersDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN email_reminders ENUM('daily','weekly','monthly','never') NOT NULL DEFAULT 'weekly'");

        User::where('email_reminders', 'daily')
            ->update([
                'email_reminders' => 'weekly'
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN email_reminders ENUM('daily','weekly','monthly','never') NOT NULL DEFAULT 'daily'");

        User::where('email_reminders', 'weekly')
            ->update([
                'email_reminders' => 'daily'
            ]);
    }
}
