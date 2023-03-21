<?php

namespace App\Console\Commands;

use App\Event;
use App\Services\DiscoverService;
use App\Services\EventService;
use App\User;
use Illuminate\Console\Command;

class WarmupCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warmup:cache
                            {--limit=}
                            {--lastActiveDays=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up users cache';

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
     *
     * 'blocked_count:' . $blocked->user_id
     * 'blocked_count:' . $blocked->user_blocked_id
     * 'event_attributes_by_mode.'.$event->id.'*'
     * 'events_members_with_ghost.'.$event->id
     * 'events_members.'.$event->id
     * 'favourites_count.' . $favorite->user_id
     * 'favourites_count.' . $favorite->user_favorite_id
     * 'cached_user.' . $user->id
     * 'user_attributes_by_mode.'.$user->id.'.*'
     *
     */
    public function handle()
    {
        define('FORCE_CACHE_FILLING', true);

        try {
            $this->fillForEvents();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        try {
            $this->fillForUsers();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        return true;
    }

    protected function fillForUsers()
    {
        $this->info('Filling cache for USERS');

        $limit = $this->option('limit');
        $lastActiveDays = $this->option('lastActiveDays');

        /** @var User $users */
        $users = User::query(null);

        if (!empty($lastActiveDays)) {
            $users = $users->where('last_active', '>=', now()->subDays($lastActiveDays)->toDateTimeString());
        }

        if ((int) $limit > 0) {
            $users = $users->orderBy('last_active', 'desc')
                ->limit($limit);
        }

        $this->info('total users '.$users->count());

        $users = $users->get();

        $bar = $this->output->createProgressBar(count($users));

        $firstUser = User::first();

        /** @var User $user */
        foreach ($users as $user) {
            // blocked_count
            $user->getBlockedCount();

            // favourites_count
            $user->getFavouritesCount();

            // Fill cache for user
            $this->fillCacheForUser($user, $firstUser, false);

            // bar
            $bar->advance();
        }


        $this->info('Cache filled for USERS');
        $bar->finish();
    }

    protected function fillCacheForUser($user, $retriever, $privacyEnabled) {
        auth()->loginUsingId($retriever->id);

        // attributes
        try {
            $user->getAttributesByMode(User::ATTRIBUTES_MODE_FULL, $retriever, $privacyEnabled);
            $user->getAttributesByMode(User::ATTRIBUTES_MODE_GENERAL, $retriever, $privacyEnabled);
            $user->getAttributesByMode(User::ATTRIBUTES_MODE_GROUP_MESSAGE, $retriever, $privacyEnabled);
            $user->getAttributesByMode(User::ATTRIBUTES_MODE_STATUS, $retriever, $privacyEnabled);
            $user->getAttributesByMode(User::ATTRIBUTES_MODE_CONVERSATION, $retriever, $privacyEnabled);
            $user->getAttributesByMode(User::ATTRIBUTES_MODE_DISCOVER, $retriever, $privacyEnabled);
        } catch (\Exception $e) {
            $this->warn($e->getMessage());
        }
    }

    protected function fillForEvents()
    {
        $this->info('Filling cache for EVENTS');

        $limit = $this->option('limit');
        $lastActiveDays = $this->option('lastActiveDays');

        /** @var User $users */
        $users = User::query(null);

        if (!empty($lastActiveDays)) {
            $users = $users->where('last_active', '>=', now()->subDays($lastActiveDays)->toDateTimeString());
        }

        if ((int) $limit > 0) {
            $users = $users->orderBy('last_active', 'desc')
                ->limit($limit);
        }

        $users = $users->get();

        $bar = $this->output->createProgressBar(count($users));

        /** @var User $user */
        foreach ($users as $user) {
            foreach([
                Event::TYPE_GUIDE,
                Event::TYPE_FUN,
                Event::TYPE_BANG,
                    ] as $type) {
                $currentDate = now()->toDateString();

                try {
                    $eventService = new EventService();
                    $eventService->setCurrentUser($user);
                    $eventService->setFilterType($type);
                    $eventService->setPage(0);
                    $eventService->setLimit(20);

                    $eventService->setExcept([]);
                    $eventService->setDate($currentDate);

                    $eventService->getEvents();
                } catch (\Exception $e) {
                    $this->warn($e->getMessage());
                }
            }

            // bar
            $bar->advance();
        }


        $this->info('Cache filled for EVENTS');
        $bar->finish();
    }
}
