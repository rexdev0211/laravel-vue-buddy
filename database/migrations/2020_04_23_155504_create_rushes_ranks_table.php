<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRushesRanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rushes_ranks', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('rush_id')
                  ->index();
            $table->integer('strip_id')
                  ->index();
            $table->integer('applauses_count');
            $table->integer('views_count');

            $table->index(['rush_id', 'strip_id']);
        });

        Schema::create('rushes_applauses', function (Blueprint $table) {
            $table->integer('rush_id')
                  ->index();
            $table->integer('strip_id')
                  ->index();
            $table->integer('user_id');
            $table->integer('applauses');
        });

        Schema::create('rushes_favorites', function (Blueprint $table) {
            $table->integer('rush_id')
                  ->index();
            $table->integer('user_id');

            $table->index(['rush_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rushes_ranks');
        Schema::dropIfExists('rushes_applauses');
        Schema::dropIfExists('rushes_favorites');
    }
}
