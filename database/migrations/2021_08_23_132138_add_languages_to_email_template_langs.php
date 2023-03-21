<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguagesToEmailTemplateLangs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE email_template_langs MODIFY COLUMN lang ENUM('en', 'de', 'fr', 'it', 'nl', 'pt', 'es')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE email_template_langs MODIFY COLUMN lang ENUM('en', 'de', 'fr', 'it', 'nl', 'pt', 'es')");
    }
}
