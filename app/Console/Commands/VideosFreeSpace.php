<?php

namespace App\Console\Commands;

use App\Services\BackendService;
use App\Services\EmailService;
use Illuminate\Console\Command;

class VideosFreeSpace extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:videos-free-space';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check free space on video server';

    private $emailService;
    private $backendService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(EmailService $emailService, BackendService $backendService)
    {
        parent::__construct();

        $this->emailService = $emailService;
        $this->backendService = $backendService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $amount = $this->backendService->getVideosServerFreeSpace();
        //critical size: 1500 Mb
        if ($amount < 1500) {
            $body = "Alert: only $amount Mb free space left on videos server. Increase disk size or remove original videos from admin area.";
            $this->line($body);
            \Log::info($body);

            $this->emailService->sendMail(config('const.ADMIN_EMAIL'), config('const.ADMIN_NAME'), 'Low disk space left on videos server', $body);
        } else {
            $message = "OK: $amount Mb free space left on videos server";
            $this->line($message);
            \Log::info($message);
        }
    }
}
