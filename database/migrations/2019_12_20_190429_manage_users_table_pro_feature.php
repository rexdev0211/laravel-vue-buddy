<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ManageUsersTableProFeature extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->timestamp('pro_expires_at')
                  ->nullable()
                  ->index()
                  ->after('email');

            $table->enum('pro_type', ['none', 'paid', 'manual', 'coupon'])
                  ->default('none')
                  ->index()
                  ->after('pro_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('pro_expires_at');
            $table->dropColumn('pro_type');
        });
    }
}
