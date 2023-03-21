<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeGoogleSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('google_subscriptions', function(Blueprint $table) {
            $table
                ->string('purchase_token', 1024)
                ->nullable()
                ->after('transaction_id');

            $table
                ->timestamp('expires_at')
                ->nullable()
                ->after('purchase_token');

            $table->dropColumn('data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('google_subscriptions', function (Blueprint $table) {
            $table->dropColumn('purchase_token');
            $table->dropColumn('expires_at');
            $table->text('data')->nullable();
        });
    }
}
