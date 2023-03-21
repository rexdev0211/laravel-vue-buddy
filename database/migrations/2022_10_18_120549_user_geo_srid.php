<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserGeoSrid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the spatial field with an SRID (e.g. 4326 WGS84 spheroid)

         Schema::table('users', function(Blueprint $table)
         {
             // Add a Point spatial data field named location with SRID 4326
             $table->point('location', 4326)->nullable()->after('gps_geom');
         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn('location');
        });
    }
}
