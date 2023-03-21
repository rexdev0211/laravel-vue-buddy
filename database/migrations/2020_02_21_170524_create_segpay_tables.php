<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSegpayTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segpay_postbacks_log', function (Blueprint $table) {
            $table->increments('id');

            $table->string('action')
                  ->nullable()
                  ->index();
            $table->integer('user_id')
                  ->nullable()
                  ->index();
            $table->string('transaction_id')
                  ->nullable()
                  ->index();
            $table->timestamp('transaction_at')
                  ->nullable();
            $table->json('data');

            $table->timestamp('created_at')
                  ->nullable();
        });

        Schema::create('segpay_purchases', function (Blueprint $table) {
            $table->increments('id');

            $table->string('action')
                  ->nullable()
                  ->index();
            $table->string('email');
            $table->string('phone');
            $table->integer('log_id');
            $table->integer('user_id')
                  ->nullable()
                  ->index();
            $table->string('type')
                  ->index();
            $table->double('price', 8, 2);
            $table->string('currency', 3);
            $table->string('stage');
            $table->boolean('approved')
                  ->nullable();
            $table->string('ip');
            $table->integer('last_4');
            $table->string('eticketid')
                  ->nullable();
            $table->string('transaction_id')
                  ->nullable()
                  ->index();
            $table->string('transaction_global_id')
                  ->nullable()
                  ->index();
            $table->timestamp('transaction_at')
                  ->nullable();
            $table->timestamp('next_bill_at')
                  ->nullable();

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
        Schema::dropIfExists('segpay_postbacks_log');
        Schema::dropIfExists('segpay_purchases');
    }
}
