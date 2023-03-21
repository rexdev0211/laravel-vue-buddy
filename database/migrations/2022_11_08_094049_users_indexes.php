<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsersIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->index(['last_active']);
            $table->index(['lat']);
            $table->index(['lng']);
            $table->index(['position']);
            $table->index(['body']);
            $table->index(['penis']);
            $table->index(['drugs']);
            $table->index(['hiv']);
            $table->index(['dob']);
            $table->index(['height']);
            $table->index(['weight']);
            $table->index(['created_at']);
            $table->index(['name']);
            $table->index(['status']);
            $table->index(['deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function(Blueprint $table) {
            $table->dropIndex(['last_active']);
            $table->dropIndex(['lat']);
            $table->dropIndex(['lng']);
            $table->dropIndex(['position']);
            $table->dropIndex(['body']);
            $table->dropIndex(['penis']);
            $table->dropIndex(['drugs']);
            $table->dropIndex(['hiv']);
            $table->dropIndex(['dob']);
            $table->dropIndex(['height']);
            $table->dropIndex(['width']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['name']);
            $table->dropIndex(['status']);
            $table->dropIndex(['deleted_at']);
        });
    }
}
