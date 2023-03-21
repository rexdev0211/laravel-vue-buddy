<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleIdFieldToFlexpaySubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('flexpay_subscriptions', function(Blueprint $table) {
            $table
                ->string('sale_id')
                ->nullable()
                ->after('transaction_id');

            $table->json('data')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('flexpay_subscriptions', function (Blueprint $table) {
            $table->dropColumn('sale_id');
            $table->text('data')->change();
        });
    }
}
