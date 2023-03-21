<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsletterScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletters_sent', function (Blueprint $table) {
            $table->date('sent_at')->primary();
            $table->integer('sent')->default(0);
        });

        Schema::create('newsletter_schedule', function (Blueprint $table) {
            $table->increments('id');

            $table->string('subject');
            $table->text('body');
            $table->boolean('in_process')
                  ->default(0);

            $table->timestamps();
        });

        Schema::create('newsletter_schedule_members', function (Blueprint $table) {
            $table->integer('user_id')->index();
            $table->integer('schedule_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('newsletters_sent');
        Schema::dropIfExists('newsletter_schedule');
        Schema::dropIfExists('newsletter_schedule_members');
    }
}
