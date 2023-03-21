<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SharingUrlsNewParams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sharing_urls', function (Blueprint $table) {
            $table->dateTime('expire_at')->nullable();
            $table->integer('views_limit')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sharing_urls', function (Blueprint $table) {
            $table->dropColumn('expire_at');
            $table->dropColumn('views_limit');
        });
    }
}
