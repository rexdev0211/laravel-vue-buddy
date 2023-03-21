<?php

namespace App\Console\Commands;

use App\Newsletter;
use App\Services\EmailService;
use App\Models\Mail\NewslettersSent;
use App\Models\Mail\NewsletterSchedule;
use App\Models\Mail\NewsletterScheduleMember;
use Illuminate\Console\Command;

class ProcessNewslettersSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:newslettersSchedule {--debug= : Debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled newsletters';

    private $emailService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('debug')) $this->info('[Send:NewslettersSchedule] Started');

        $schedule = NewsletterSchedule::whereInProcess(0)
                                      ->with('recipients')
                                      ->oldest()
                                      ->first();

        $sent = NewslettersSent::firstOrCreate([
            'sent_at' => date('Y-m-d', strtotime('now')),
        ]);
        $limit  = config('mail.sparkpost_daily_limit') - $sent->sent;
        $sentTo = [];

        if ($schedule) {
            $schedule->in_process = 1;
            $schedule->save();

            if ($schedule->recipients->count() == 0) {
                NewsletterScheduleMember::whereScheduleId($schedule->id)->delete();
                $schedule->delete();

                if ($this->option('debug')) $this->info('[Send:NewslettersSchedule] This newsletters schedule is empty and now deleted from queue.');
            } else {
                $recipients = [];
                foreach ($schedule->recipients as $recipient) {
                    if (count($sentTo) < $limit) {
                        $email = $recipient->email ?: $recipient->email_orig;

                        $sentTo[$email] = $recipient->id;

                        /** @var Newsletter $newsletter */
                        $newsletter = Newsletter::where('user_id', $recipient->id)
                            ->orWhere('email', $email)
                            ->first();

                        if (null === $newsletter) {
                            continue;
                        }

                        if (!$email) {
                            $email = $newsletter->email;

                            $sentTo[$email] = $recipient->id;
                        }

                        $recipients[] = [
                            'address' => [
                                'name'  => $recipient->name,
                                'email' => $email,
                            ],
                            'substitution_data' => [
                                'FULL_NAME'        => $recipient->name,
                                'EMAIL'            => $email,
                                'UNSUBSCRIBE_LINK' => route('newsletter.unsubscribe', [$newsletter->id, $newsletter->hash_key]),
                            ],
                        ];
                    } else {
                        break;
                    }
                }

                if (count($sentTo)) {
                    $chunkCount = count($recipients) > 100 ? 100 : count($recipients);

                    $recipientsArray = array_chunk($recipients, $chunkCount);
                    foreach ($recipientsArray as $subRecipients) {
                        sleep(5);
                        $response = $this->emailService->sendSparkpostMassMail($schedule->subject, $schedule->body, $subRecipients);

                        if ($response['status'] == 'ok') {
                            $clear = [];
                            foreach ($subRecipients as $subRecipient) {
                                $clear[] = $sentTo[$subRecipient['address']['email']];
                            }

                            if (count($clear)) {
                                NewsletterScheduleMember::whereScheduleId($schedule->id)
                                                        ->whereIn('user_id', $clear)
                                                        ->delete();

                                if ($this->option('debug')) $this->info('[Send:NewslettersSchedule] '.count($clear).' messages sent for newsletter schedule #'.$schedule->id);
                            }
                        } else {
                            $errorMessage = " Failed to send mail to ". count($subRecipients) ." users for newsletter schedule #". $schedule->id .": " . $response['code'] . ' - '.$response['message'] . '. ';

                            if ($this->option('debug')) $this->info('[Send:NewslettersSchedule] '.$errorMessage);
                            \Log::error($errorMessage . print_r($subRecipients, 1));
                        }
                    }
                }

                $schedule->in_process = 0;
                $schedule->save();
            }
        } else {
            if ($this->option('debug')) $this->info('[Send:NewslettersSchedule] There is no newsletters in queue.');
        }

        $sent->sent += count($sentTo);
        $sent->save();

        if ($this->option('debug')) $this->info('[Send:NewslettersSchedule] SparkPost limit for today is: '.(config('mail.sparkpost_daily_limit') - ($limit - count($sentTo))).'/'.config('mail.sparkpost_daily_limit'));
        if ($this->option('debug')) $this->info('[Send:NewslettersSchedule] Done');
    }
}
