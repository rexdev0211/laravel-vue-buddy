<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlexpaySubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flexpay_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->string('package_id')->nullable()->index();
            $table->string('package_name')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('data');
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flexpay_subscriptions');
    }
}
