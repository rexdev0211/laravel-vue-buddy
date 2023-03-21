<?php

use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ManageUsersTable20200502 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'suspended', 'deactivated', 'ghosted') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $users = User::where('status', 'ghosted')
                     ->update([
                         'status' => 'suspended',
                     ]);

        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'suspended', 'deactivated') NOT NULL DEFAULT 'active'");
    }
}
