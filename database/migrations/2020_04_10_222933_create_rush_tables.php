<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRushTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rushes', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')
                  ->index();

            $table->timestamps();
        });

        Schema::create('rushes_strips', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('rush_id')
                  ->index();
            $table->enum('type', ['bubble', 'image', 'video']);
            $table->integer('image_id')
                  ->nullable();
            $table->integer('video_id')
                  ->nullable();
            $table->string('image_path')
                  ->nullable();
            $table->string('video_path')
                  ->nullable();
            $table->string('title');
            $table->text('message');

            $table->timestamps();
        });

        Schema::create('rushes_medias', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')
                  ->index();
            $table->enum('type', ['image', 'video']);
            $table->string('path');
            $table->string('extension');
            $table->enum('status', ['processed', 'waiting'])
                  ->default('waiting');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rushes_medias');
        Schema::dropIfExists('rushes_strips');
        Schema::dropIfExists('rushes');
    }
}
