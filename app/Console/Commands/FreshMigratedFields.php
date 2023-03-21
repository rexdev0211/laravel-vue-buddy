<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class FreshMigratedFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:fresh-migrated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fresh migrated fields';

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
        // Notifications
        DB::table('users')->where('has_unseen_notif_general', 'yes')->update(['has_notifications' => 1]);
        DB::table('users')->where('has_unseen_notif_general', 'no')->update(['has_notifications' => 0]);

        DB::table('users')->where('has_unseen_notif_taps', 'yes')->update(['has_new_notifications' => 1]);
        DB::table('users')->where('has_unseen_notif_taps', 'no')->update(['has_new_notifications' => 0]);

        DB::table('users')->where('has_unseen_notif_visitors', 'yes')->update(['has_new_visitors' => 1]);
        DB::table('users')->where('has_unseen_notif_visitors', 'no')->update(['has_new_visitors' => 0]);

        DB::table('users')->where('has_unseen_notices', 'yes')->update(['has_new_messages' => 1]);
        DB::table('users')->where('has_unseen_notices', 'no')->update(['has_new_messages' => 0]);

        // Messages
        DB::table('messages')->where('is_read_notification_checked', 1)->update(['is_read_cloak' => 1]);
        DB::table('messages')->where('is_read_notification_checked', 0)->update(['is_read_cloak' => 0]);

        return 0;
    }
}
