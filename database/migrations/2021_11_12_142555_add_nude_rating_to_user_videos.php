<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\UserVideo;

class AddNudeRatingToUserVideos extends Migration
{
    /**
     * @var int
     */
    protected $chunk = 4000;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql')->table('user_videos', function (Blueprint $table) {
            $table->float('nudity_rating')->nullable()->after('status');
        });

        UserVideo::chunk($this->chunk, function ($videos) {
            foreach ($videos as $video) {
                $video->update([
                   'nudity_rating' => config('const.START_NUDITY_RATING') + 0.1
                ]);
            }
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->table('user_videos', function (Blueprint $table) {
            $table->dropColumn('nudity_rating');
        });
    }
}
