<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SpatialIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function(Blueprint $table) {
            $table->point('location_geom', 4326)->spatialIndex()->nullable(false)->change();
        });

        Schema::table('users', function(Blueprint $table) {
            $table->point('location', 4326)->spatialIndex()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function(Blueprint $table) {
            $table->dropSpatialIndex('location_geom');
        });

        Schema::table('users', function(Blueprint $table) {
            $table->dropSpatialIndex('location');
        });
    }
}
