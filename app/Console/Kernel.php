<?php

namespace App\Console;

use App\Console\Commands\CleanOauthRefreshTokens;
use App\Console\Commands\CleanOldEmails;
use App\Console\Commands\ClearAllCacheCommand;
use App\Console\Commands\DisableNewsletterForUsers;
use App\Console\Commands\FixShowAgeCommand;
use App\Console\Commands\FixUserCoordinatesCommand;
use App\Console\Commands\GenerateMassMessages;
use App\Console\Commands\RecreateNotExistsNewsletter;
use App\Console\Commands\TransferOldLocationToNewSridCommand;
use App\Console\Commands\WarmupCacheCommand;
use Composer\Command\ClearCacheCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CheckConversationCountsCommand;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Test::class,
        Commands\Seed::class,
        Commands\ClearRush::class,
        Commands\DailyNotifications::class,
        Commands\PushNotifications::class,
        Commands\WeeklyNotifications::class,
        Commands\MonthlyNotifications::class,
        Commands\MonthlyLoginReminder::class,
        Commands\SpammersList::class,
        Commands\DbCleanup::class,
        Commands\PhotosNudeRating::class,
        Commands\EventPhotosNudeRating::class,
        Commands\VideosFreeSpace::class,
        Commands\RemoveSuspendedUsers::class,
        Commands\SetStaffUsersDefaultDiscreetMode::class,
        Commands\ProcessNewslettersSchedule::class,
        Commands\MarkMessagesForSuspendedUsers::class,
        Commands\ClearBlocksAndFavorites::class,
        Commands\FreshMigratedFields::class,
        Commands\ListMissingVideos::class,
        Commands\RenewSubscriptions::class,
        Commands\AssignBuddyLink::class,
        Commands\GenerateProAccounts::class,
        Commands\FixGpsGeomCommand::class,
        Commands\SetGhostedUsersToMessages::class,
        Commands\SetBlockedUsersToMessages::class,
        Commands\TransferringRecordsOfLastRead::class,
        CheckConversationCountsCommand::class,

        Commands\Subscriptions\AppleGetLatestReceipts::class,
        Commands\Subscriptions\GoogleGetLatestReceipts::class,

        Commands\ClearConversationsCache::class,
        Commands\CreateConversationsCache::class,
        Commands\RebuildConversationsCache::class,
        Commands\CheckCacheConversationDuplicates::class,
        Commands\ImportCountriesFromJSON::class,

        Commands\RemoveOldEvents::class,
        Commands\CheckCacheConversationDuplicates::class,

        FixShowAgeCommand::class,
        DisableNewsletterForUsers::class,
        RecreateNotExistsNewsletter::class,

        GenerateMassMessages::class,

        CleanOldEmails::class,
        ClearAllCacheCommand::class,
        WarmupCacheCommand::class,
        CleanOauthRefreshTokens::class,
        FixUserCoordinatesCommand::class,
        TransferOldLocationToNewSridCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cron:daily_notifications')->dailyAt('10:00');
        $schedule->command('cron:weekly_notifications')->weeklyOn(7, '11:00');
        $schedule->command('cron:monthly_notifications')->monthlyOn(1, '11:00');
        $schedule->command('cron:monthly_login_reminder')->dailyAt('09:00');
        $schedule->command('clean:old_emails')->hourly();
        $schedule->command('clean:oauth_refresh_tokens')->daily()->at('03:00');

        $schedule->command('cron:clear_blocks_and_favorites')->weeklyOn(7, '05:00');
        $schedule->command('cron:db_cleanup')->dailyAt('04:00');
        $schedule->command('cron:spammers_list')->hourly();

        $schedule->command('cron:remove-old-events')->weeklyOn(7, '03:00');

        $schedule->command('send:newslettersSchedule')
            ->everyFifteenMinutes()
            ->withoutOverlapping(true);

        $schedule->command('check:conversation_counts')
            ->twiceDaily()
            ->withoutOverlapping();

        // $schedule->command('cron:clear_rush')->everyFiveMinutes();
        // $schedule->command('cron:videos-free-space')->hourly();
        // $schedule->command('cron:remove-suspended-users')->daily();

        //$schedule->command('cron:assign_buddy_link')->everyFiveMinutes();
        //$schedule->command('cron:renew-subscriptions')->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
