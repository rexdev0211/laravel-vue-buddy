<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMessageIsReadCloakField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_read_cloak')->default(0)->after('is_read');
        });

        DB::table('messages')->where('is_read_notification_checked', 1)->update(['is_read_cloak' => 1]);
        DB::table('messages')->where('is_read_notification_checked', 0)->update(['is_read_cloak' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('is_read_cloak');
        });
    }
}
