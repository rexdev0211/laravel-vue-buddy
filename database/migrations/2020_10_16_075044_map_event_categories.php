<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Event;

class MapEventCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function($table) {
            $table
                ->string('type', 32)
                ->default(Event::TYPE_FRIENDS)
                ->after('category');
        });

        DB::table('events')->where('category', 'fun')->update(['type' => Event::TYPE_FRIENDS]);
        DB::table('events')->where('category', 'sex')->update(['type' => Event::TYPE_FUN]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function($table) {
            $table->dropColumn('type');
        });
    }
}
