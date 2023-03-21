<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\UserReported;

class ManageUserReportedMap20210225 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE user_reported_map MODIFY COLUMN report_type ENUM('harassment', 'fake', 'spam', 'under_age', 'other', 'illegal') NOT NULL DEFAULT 'harassment'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        UserReported::whereIn('report_type', ['illegal'])
                   ->update([
                       'report_type' => 'other',
                   ]);

        DB::statement("ALTER TABLE user_reported_map MODIFY COLUMN report_type ENUM('harassment', 'fake', 'spam', 'under_age', 'other') NOT NULL DEFAULT 'harassment'");
    }
}
