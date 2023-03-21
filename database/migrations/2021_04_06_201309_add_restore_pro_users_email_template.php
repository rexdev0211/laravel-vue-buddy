<?php

use App\EmailTemplate;
use App\EmailTemplateLang;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRestoreProUsersEmailTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sortOrder = EmailTemplate::max('sort_order');

        $template = EmailTemplate::firstOrNew([
            'name' => 'pro_account_restored',
        ]);

        $template->sort_order = $sortOrder ? $sortOrder + 1 : 1;
        $template->notes      = "Sent to: User\nInfo: Send on PRO accounts restored process\nVariables: {NICKNAME}, {EMAIL}, {PASSWORD}";
        $template->save();

        $trans = EmailTemplateLang::firstOrNew([
            'email_template_id' => $template->id,
            'lang'              => 'en',
        ]);

        $trans->subject = 'Your BareBuddy PRO account restored!';
        $trans->body    = '<p>Hey mate! We restored your pro account.</p><p>Automatically Generated Nickname: {NICKNAME}</p><p>Login: {EMAIL}</p><p>Password: {PASSWORD}</p>';
        $trans->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $template = EmailTemplate::where('name', 'pro_account_restored')->first();

        if ($template) {
            EmailTemplateLang::where('email_template_id', $template->id)->delete();

            $template->delete();
        }
    }
}
