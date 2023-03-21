<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Event\EventReport;

class ManageEventReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE event_reports MODIFY COLUMN reason ENUM('harassment', 'wrong_category', 'illegal', 'fake', 'spam', 'under_age', 'other') NOT NULL DEFAULT 'other'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        EventReport::whereIn('reason', ['wrong_category', 'illegal'])
                   ->update([
                       'reason' => 'other',
                   ]);

        DB::statement("ALTER TABLE event_reports MODIFY COLUMN reason ENUM('harassment', 'fake', 'spam', 'under_age', 'other') NOT NULL DEFAULT 'other'");
    }
}
