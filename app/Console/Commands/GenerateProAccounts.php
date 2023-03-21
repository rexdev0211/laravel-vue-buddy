<?php

namespace App\Console\Commands;

use DB;
use Excel;
use Carbon\Carbon;
use App\Imports\ProAccount;
use App\Mail\EmailTemplateBuilder;

use App\User;
use App\EmailTemplate;
use App\EmailTemplateLang;
use App\Models\Payment\SegpayOldPurchases;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class GenerateProAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:pro_accounts {--debug= : Debug}
                                                  {--send= : Send email notifications}
                                                  {--send-test= : Send one email notification to provided test email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate PRO Accounts based on Active Members Report file data';

    /**
     * The console command debug message key.
     *
     * @var string
     */
    protected $infoMessageKey = '[Generate: PRO Accounts]';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->showMsg('Started.');

        $path = 'segpay/active_members_report.xlsx';
        $accounts = (new ProAccount)->withOutput($this->output);

        /* Get Accounts data from xlsx file */
        Excel::import($accounts, $path);
        $list = $accounts->getList();

        $this->showMsg(count($list).' active PRO subscriptions found. Processing...');

        if ($this->option('debug')) {
            $bar = $this->output->createProgressBar(count($list));

            $bar->start();
        }

        $maxCurrentId = User::max('id');
        $sendEmails = [];
        foreach ($list as $row) {
            /* Firstly we need to create new user */
            $user = User::firstOrNew([
                'email' => $row['email'],
            ]);

            if (!$user->id) {
                $maxCurrentId = $maxCurrentId + 1;

                $user->name           = 'buddy-user-'.($maxCurrentId + 1);
                $user->dob            = '2000-01-01';
                $user->language       = 'en';
                $user->gps_geom       = DB::raw("POINT(13.2846506, 52.5069704)");
                $user->lat            = 52.5069704;
                $user->lng            = 13.2846506;
            }

            /* Add 2 additional months for subscriptions */
            $user->pro_expires_at = Carbon::parse($row['expired_at'])->addMonths(2);
            $user->pro_type       = 'paid';

            /* Change password even for already created user */
            $password = bin2hex(random_bytes(10));
            $user->password = \Hash::make($password);
            $user->save();

            /* Save data to Segpay Old Purchases table */
            $relation = SegpayOldPurchases::firstOrNew([
                'purchase_id' => $row['purchase_id'],
            ]);

            $relation->user_id = $user->id;
            $relation->save();

            /* Prepare data for email */
            $sendEmails[$row['email']] = [
                'email'    => $row['email'],
                'username' => $user->name,
                'password' => $password,
            ];

            if ($this->option('debug')) {
                $bar->advance();
            }
        }

        if ($this->option('debug')) {
            $bar->finish();
            $this->info('');
        }

        if ($this->option('send')) {
            if (count($sendEmails)) {
                $template = EmailTemplate::where('name', 'pro_account_restored')->first();

                if ($template) {
                    $templateLang = EmailTemplateLang::where('email_template_id', $template->id)->where('lang', 'en')->first();

                    if ($templateLang || !$templateLang->subject || !$templateLang->body) {
                        $this->showMsg('Sending email notifications...');

                        if ($this->option('debug')) {
                            $bar = $this->output->createProgressBar(count($sendEmails));

                            $bar->start();
                        }

                        /* Send email notifications */
                        foreach ($sendEmails as $email) {
                            $templateVars   = ['{NICKNAME}', '{EMAIL}', '{PASSWORD}'];
                            $templateValues = [$email['username'], $email['email'], $email['password']];

                            $subject = str_replace($templateVars, $templateValues, $templateLang['subject']);
                            $body    = str_replace($templateVars, $templateValues, $templateLang['body']);

                            Mail::to([['email' => $email['email'], 'name' => $email['username']]])->send(new EmailTemplateBuilder($subject, $body));

                            if ($this->option('debug')) {
                                $bar->advance();
                            }
                        }

                        if ($this->option('debug')) {
                            $bar->finish();
                            $this->info('');
                        }
                    } else {
                        $this->showMsg('Email notifications weren\'t send. Email Template "en" translate is missing (or subject/body is empty).', 'error');
                    }
                } else {
                    $this->showMsg('Email notifications weren\'t send. Email Template is missing.', 'error');
                }
            }
        } else {
            $this->showMsg('Email notifications weren\'t send. Use --send=true option to send email notifications.');
        }

        if ($this->option('send-test')) {
            $validator = Validator::make(['email' => $this->option('send-test')], ['email' => 'required|email']);

            if (!$validator->fails()) {
                $template = EmailTemplate::where('name', 'pro_account_restored')->first();

                if ($template) {
                    $templateLang = EmailTemplateLang::where('email_template_id', $template->id)->where('lang', 'en')->first();

                    if ($templateLang || !$templateLang->subject || !$templateLang->body) {
                        /* Get random email notification */
                        $notification = collect($sendEmails)->random();

                        $templateVars   = ['{NICKNAME}', '{EMAIL}', '{PASSWORD}'];
                        $templateValues = [$notification['username'], $notification['email'], $notification['password']];

                        $subject = str_replace($templateVars, $templateValues, $templateLang['subject']);
                        $body    = str_replace($templateVars, $templateValues, $templateLang['body']);

                        Mail::to([['email' => $this->option('send-test'), 'name' => $notification['username']]])->send(new EmailTemplateBuilder($subject, $body));
                        $this->showMsg('Test email notification successfuly sent.');
                    } else {
                        $this->showMsg('Test email notification wasn\'t send. Email Template "en" translate is missing (or subject/body is empty).', 'error');
                    }
                } else {
                    $this->showMsg('Test email notification wasn\'t send. Email Template is missing.', 'error');
                }
            } else {
                $this->showMsg('Only valid email address allowed for --send-test param.');
            }
        } else {
            $this->showMsg('Test email notification wasn\'t send. Use --send-test=some@example.com option to send test email notification.');
        }

        $this->showMsg('Done.');
    }

    /**
     * Show logs
     * @param [string] $msg  [Content of message]
     * @param [string] $type [Type of message]
     * @return null
    */
    private function showMsg($msg, $type = null)
    {
        if ($this->option('debug')) {
            switch ($type) {
                case 'warning':
                    $this->warn(Carbon::now()->format('Y-m-d H:i:s').' - '. $this->infoMessageKey .' '.$msg);
                break;

                case 'error':
                    $this->error(Carbon::now()->format('Y-m-d H:i:s').' - '. $this->infoMessageKey .' '.$msg);
                break;

                default:
                    $this->info(Carbon::now()->format('Y-m-d H:i:s').' - '. $this->infoMessageKey .' '.$msg);
            }
        }
    }
}
