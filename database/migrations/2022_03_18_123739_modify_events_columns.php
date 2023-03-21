<?php

use App\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ModifyEventsColumns extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
                    ALTER TABLE events 
                    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                    MODIFY COLUMN title VARCHAR(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, 
                    MODIFY COLUMN description VARCHAR(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
                    ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Event::whereRaw('LENGTH(title) > 40 OR LENGTH(description) > 510')
             ->orderByDesc('created_at')
             ->chunk(500, function ($events) {
                 foreach ($events as $event) {
                     if (utf8_decode($event->title)) {
                         $event->title = str_limit($event->title, 30);
                     } else {
                         $event->title = substr(str_limit($event->title, 30), 0, -3);
                     }

                     if (utf8_decode($event->description)) {
                         $event->description = str_limit($event->description, 510);
                     } else {
                         $event->description = substr(str_limit($event->description, 510), 0, -3);
                     }

                     $event->save();
                 }
             });

        DB::statement('
                            ALTER TABLE events
                            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                            MODIFY COLUMN title VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                            MODIFY COLUMN description VARCHAR(510) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
                        ');
    }
}
