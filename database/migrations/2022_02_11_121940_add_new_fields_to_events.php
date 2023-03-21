<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToEvents extends Migration
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
        Schema::connection($this->connection)->table('events', function (Blueprint $table) {
            $table->string('venue')->after('is_sticky')->nullable();
            $table->string('website')->after('venue')->nullable();
            $table->string('name')->after('website')->nullable();
            $table->string('contact')->after('name')->nullable();
            $table->text('note')->after('contact')->nullable();
            $table->enum('featured', ['yes', 'no'])->after('note')->default('no');
        });

        DB::connection($this->connection)->statement("ALTER TABLE events CHANGE COLUMN status status ENUM('active', 'suspended', 'pending', 'approved', 'declined') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \App\Event::where('type', 'guide')
                  ->chunk(1000, function ($events) {
                      foreach ($events as $event) {
                          $event->type = 'friends';
                          $event->status = 'active';
                          $event->save();
                      }
                  });

        Schema::connection($this->connection)->table('events', function (Blueprint $table) {
            $table->dropColumn(['venue', 'website', 'name', 'contact', 'note', 'featured']);
        });

        DB::connection($this->connection)->statement("ALTER TABLE events CHANGE COLUMN status status ENUM('active', 'suspended') NOT NULL DEFAULT 'active'");
    }
}
