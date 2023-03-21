<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGooglePostbackLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->string('package_id')->index();
            $table->string('package_name');
            $table->string('transaction_id');
            $table->text('data');
            $table->timestamps();
        });

        Schema::create('google_postbacks_log', function (Blueprint $table) {
            $table->id();
            $table->string('action')->nullable();
            $table->integer('user_id')->nullable()->index();
            $table->string('transaction_id')->nullable();
            $table->json('data');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('google_postbacks_log');
        Schema::dropIfExists('google_subscriptions');
    }
}
