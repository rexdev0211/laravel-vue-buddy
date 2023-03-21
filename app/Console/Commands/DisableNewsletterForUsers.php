<?php

namespace App\Console\Commands;

use App\Newsletter;
use App\User;
use Illuminate\Console\Command;

class DisableNewsletterForUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'removed_users:disable_newsletter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark removed users newsletter flag as disabled.';

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
        $users = User::whereIn('status', ['suspended', 'ghosted'])
                     ->get();

        $this->info('found '.count($users).' users');

        /** @var User $user */
        foreach ($users as $user) {
            /** @var Newsletter $newsletters */
            $newsletters = Newsletter::where(function($q) use($user) {
                $q->where('email', $user->email)
                    ->orWhere('user_id', $user->id);
            })
                ->where('subscribed', 'yes')
                ->get();

            /** @var Newsletter $newsletter */
            foreach ($newsletters as $newsletter) {
                $this->info('update subscription for user '.$user->email.' to flag no');

                $newsletter->subscribed = 'no';
                $newsletter->save();
            }
        }

        // ----

//        $this->info('get subscriptions with forced deleted users');
//
//        /** @var Newsletter $newsletters */
//        $newsletters = Newsletter::where('subscribed', 'yes')
//            ->get();
//
//        $this->line('=================');
//        $this->info('found '.count($newsletters).' to check their users');
//
//        /** @var Newsletter $newsletter */
//        foreach ($newsletters as $newsletter) {
//            /** @var User $checkUser */
//            $checkUser = User::where('email', $newsletter->email)
//                ->orWhere('id', $newsletter->user_id)
//                ->first();
//
//            if (null !== $checkUser) {
//                continue;
//            }
//
//            $newsletter->subscribed = 'no';
//            $newsletter->save();
//
//            $this->info('set subscribed NO for email '.$newsletter->email);
//        }

        return 0;
    }
}
