<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppleSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apple_subscriptions', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')
                  ->nullable()
                  ->index();
            $table->string('package_id');
            $table->string('transaction_id');
            $table->text('latest_reciept');
            $table->timestamp('expires_at')
                  ->nullable();

            $table->timestamps();
        });

        Schema::create('apple_postbacks_log', function (Blueprint $table) {
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
            $table->json('data');

            $table->timestamp('created_at')
                  ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apple_postbacks_log');
        Schema::dropIfExists('apple_subscriptions');
    }
}
