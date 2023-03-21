<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOldWelcomes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $languages = ['en', 'de', 'es', 'fr', 'it', 'nl', 'pt'];

        foreach ($languages as $language) {
            $textMessage = trans('message.hello', [], $language);

            if (empty($textMessage)) {
                continue;
            }

            \Illuminate\Support\Facades\DB::connection('mongodb')
                ->table('messages')
                ->where('is_read', 'yes') // who never saw this message, will see it, messages not removed.
                ->where('message', 'like', $textMessage)
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
