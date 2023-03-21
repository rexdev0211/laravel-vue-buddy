<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncludePhotosModeration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_photos', function (Blueprint $table) {
            $table->boolean('is_included_in_rating')->default(false)->after('manual_rating');
        });

        Schema::table('user_videos', function (Blueprint $table) {
            $table->boolean('is_included_in_rating')->default(false)->after('manual_rating');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_photos', function (Blueprint $table) {
            $table->dropColumn('is_included_in_rating');
        });

        Schema::table('user_videos', function (Blueprint $table) {
            $table->dropColumn('is_included_in_rating');
        });
    }
}
