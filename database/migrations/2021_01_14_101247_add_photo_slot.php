<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\UserPhoto;

class AddPhotoSlot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_photos', function(Blueprint $table) {
            $table
                ->enum('slot', ['clear', 'adult'])
                ->default(null)
                ->nullable()
                ->after('is_default');
        });

        // Set clear slot
        DB::table('user_photos')
            ->where([
                'is_default' => 'yes',
                'visible_to' => 'public'
            ])
            ->where(function($query) {
                $query
                    ->where('nudity_rating', '<=', 0.4)
                    ->orWhereIn('manual_rating', [UserPhoto::RATING_CLEAR]);
            })
            ->update(['slot' => 'clear']);

        // Set adult slot
        DB::table('user_photos')
            ->where([
                'is_default' => 'yes',
                'visible_to' => 'public'
            ])
            ->where(function($query) {
                $query
                    ->where('nudity_rating', '>', 0.4)
                    ->orWhereIn('manual_rating', [UserPhoto::RATING_SOFT, UserPhoto::RATING_ADULT, UserPhoto::RATING_PROHIBITED]);
            })
            ->update(['slot' => 'adult']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_photos', function($table) {
            $table->dropColumn('slot');
        });
    }
}
