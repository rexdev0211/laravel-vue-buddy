<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwokchargeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twokcharge_logs', function (Blueprint $table) {
            $table->increments('id');

            $table->string('action');
            $table->integer('user_id')->nullable();
            $table->string('transaction_id');
            $table->timestamp('transaction_at');
            $table->json('data');

            $table->timestamps();
        });

        Schema::create('twokcharge_transactions', function (Blueprint $table) {
            $table->increments('id');

            $table->string('transaction_id')->nullable();
            $table->string('package_id')->nullable();
            $table->string('customer_id');
            $table->integer('user_id');
            $table->string('email');
            $table->string('status');
            $table->string('ip');
            $table->integer('log_id');
            $table->double('amount', 8, 2);
            $table->string('currency', 3);
            $table->string('payment_option');
            $table->string('redirect');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twokcharge_logs');
        Schema::dropIfExists('twokcharge_transactions');
    }
}
