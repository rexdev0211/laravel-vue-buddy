<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToUserPhotos extends Migration
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
        Schema::connection($this->connection)->table('user_photos', function (Blueprint $table) {
            $table->index('status', 'status');
            $table->index('manual_rating', 'manual_rating');
            $table->index('photo', 'photo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->table('user_photos', function (Blueprint $table) {
            $table->dropIndex('status');
            $table->dropIndex('manual_rating');
            $table->dropIndex('photo');
        });
    }
}
