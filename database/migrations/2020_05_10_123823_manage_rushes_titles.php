<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Rush\Rush;
use App\Models\Rush\RushStrip;

class ManageRushesTitles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rushes', function (Blueprint $table) {
            $table->string('title')
                  ->defaut('')
                  ->after('user_id');
        });

        $rushes = Rush::with('latest_strip')
                      ->get();

        foreach ($rushes as $rush) {
            $rush->title = $rush->latest_strip->title;
            $rush->save();
        }

        Schema::table('rushes_strips', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rushes_strips', function (Blueprint $table) {
            $table->string('title')
                  ->defaut('')
                  ->after('profile_attached');
        });

        $rushes = Rush::all();

        foreach ($rushes as $rush) {
            RushStrip::where('rush_id', $rush->id)
                     ->update([
                         'title' => $rush->title,
                     ]);
        }

        Schema::table('rushes', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
}
