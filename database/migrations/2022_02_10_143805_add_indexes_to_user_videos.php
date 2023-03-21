<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToUserVideos extends Migration
{
    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->table('user_videos', function (Blueprint $table) {
            $table->index('video_name', 'video_name');
            $table->index('nudity_rating', 'nudity_rating');
            $table->index('manual_rating', 'manual_rating');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->table('user_videos', function (Blueprint $table) {
            $table->dropIndex('video_name');
            $table->dropIndex('nudity_rating');
            $table->dropIndex('manual_rating');
        });
    }
}
