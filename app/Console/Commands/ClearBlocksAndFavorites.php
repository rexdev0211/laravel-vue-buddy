<?php

namespace App\Console\Commands;

use App\UserBlocked;
use App\UserFavorite;

use Illuminate\Console\Command;

class ClearBlocksAndFavorites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:clear_blocks_and_favorites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Blocked and Favorites';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        $this->info('[Clear:BlockedAndFavorites] Started');

        $blocked = UserBlocked::with('blocked')
                              ->with('blocker')
                              ->whereHas('blocked', function($query) {
                                  $query->whereNotNull('deleted_at')
                                        ->orWhere('status', 'suspended');
                              })
                              ->orWhere(function($query){
                                  $query->whereHas('blocker', function($subquery){
                                      $subquery->whereNotNull('deleted_at')
                                               ->orWhere('status', 'suspended');
                                  });
                              })
                              ->orWhere(function($query){
                                  $query->whereDoesntHave('blocked');
                              })
                              ->orWhere(function($query){
                                  $query->whereDoesntHave('blocker');
                              });

        $this->info('[Clear:BlockedAndFavorites] '.$blocked->count().' rows with deleted/suspended blocker/blocked found.');
        $blocked->delete();

        $favorites = UserFavorite::with('favoriter')
                                 ->with('favorited')
                                 ->whereHas('favorited', function($query) {
                                     $query->whereNotNull('deleted_at')
                                           ->orWhere('status', 'suspended');
                                 })
                                 ->orWhere(function($query) {
                                     $query->whereHas('favoriter', function($subquery) {
                                         $subquery->whereNotNull('deleted_at')
                                                  ->orWhere('status', 'suspended');
                                     });
                                 })
                                 ->orWhere(function($query) {
                                     $query->whereDoesntHave('favorited');
                                 })
                                 ->orWhere(function($query) {
                                     $query->whereDoesntHave('favoriter');
                                 });

        $this->info('[Clear:BlockedAndFavorites] '.$favorites->count().' rows with deleted/suspended favoriter/favorited found.');
        $favorites->delete();
        $this->info('[Clear:BlockedAndFavorites] Finished');
    }
}
