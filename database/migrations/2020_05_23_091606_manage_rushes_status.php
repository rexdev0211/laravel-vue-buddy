<?php

use App\Models\Rush\Rush;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ManageRushesStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE rushes MODIFY COLUMN status ENUM('active', 'suspended', 'deleted') NOT NULL DEFAULT 'active'");

        Schema::table('rushes_strips', function (Blueprint $table) {
            $table->boolean('is_deleted')
                  ->default(0)
                  ->after('message');
        });

        Schema::table('rushes_ranks', function (Blueprint $table) {
            $table->boolean('is_deleted')
                  ->default(0)
                  ->after('views_count');
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
            $table->dropColumn('is_deleted');
        });

        Schema::table('rushes_ranks', function (Blueprint $table) {
            $table->dropColumn('is_deleted');
        });

        $rushes = Rush::where('status', 'deleted')
                      ->update([
                          'status' => 'active',
                      ]);

        DB::statement("ALTER TABLE rushes MODIFY COLUMN status ENUM('active', 'suspended') NOT NULL DEFAULT 'active'");
    }
}
