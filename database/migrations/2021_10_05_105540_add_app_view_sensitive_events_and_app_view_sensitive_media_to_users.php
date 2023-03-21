<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\User;

class AddAppViewSensitiveEventsAndAppViewSensitiveMediaToUsers extends Migration
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
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('app_view_sensitive_events')->default(0)->after('view_sensitive_events');
            $table->tinyInteger('app_view_sensitive_media')->default(0)->after('view_sensitive_media');
            $table->tinyInteger('web_view_sensitive_content')->default(1)->after('app_view_sensitive_media');
        });

        User::chunk($this->chunk, function ($users) {
            foreach ($users as $user) {
                $user->update([
                    'app_view_sensitive_events' => $user->view_sensitive_events === 'yes' ? 1 : 0,
                    'app_view_sensitive_media' => $user->view_sensitive_media
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
        User::chunk($this->chunk, function ($users) {
            foreach ($users as $user) {
                $user->update([
                    'view_sensitive_events' => $user->app_view_sensitive_events ? 'yes' : 'no',
                    'view_sensitive_media' => $user->app_view_sensitive_media
                ]);
            }
        });


        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('app_view_sensitive_events');
            $table->dropColumn('app_view_sensitive_media');
            $table->dropColumn('web_view_sensitive_content');
        });
    }
}
