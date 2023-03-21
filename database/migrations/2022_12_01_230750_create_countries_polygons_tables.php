<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesPolygonsTables extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries_polygons', function (Blueprint $table) {
            $table->id();
            $table->polygon('polygon');
            // $table->foreignId('country_id')
            //       ->constrained('countries')
            //       ->onUpdate('cascade')
            //       ->onDelete('cascade');
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
        Schema::dropIfExists('countries_polygons');
    }
};
