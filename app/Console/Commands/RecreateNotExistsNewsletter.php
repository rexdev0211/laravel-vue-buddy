<?php

namespace App\Console\Commands;

use App\Newsletter;
use App\Repositories\NewsletterRepository;
use App\User;
use Illuminate\Console\Command;

class RecreateNotExistsNewsletter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletter:recreate_not_exists';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recreate newsletters';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var User $users */
        $users = User::whereDoesntHave('newsletter')
                     ->get();

        $this->info('found '.count($users).' users');
        $newslettersCreated = 0;

        /** @var User $user */
        foreach ($users as $user) {
            $this->info('work with user '.$user->email);

            $checkExistsNewsletter = Newsletter::where('email', $user->email)->count();

            if ($checkExistsNewsletter > 0) {
                continue;
            }

            /** @var NewsletterRepository $newsletterRepository */
            $newsletterRepository = new NewsletterRepository();
            $newsletterRepository->createOrUpdateUserNewsletter($user);

            $newslettersCreated++;
            $this->info('newsletter created');
        }

        $this->line('========');
        $this->info('newsletters created: '.$newslettersCreated);
        $this->info('users checked: '.count($users));

        return 0;
    }
}
