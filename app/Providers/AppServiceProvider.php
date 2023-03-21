<?php

namespace App\Providers;

use App\Event;
use App\EventMembership;
use App\Models\Payment\AppleSubscription;
use App\Models\Payment\FlexpaySubscription;
use App\Models\Payment\GoogleSubscription;
use App\Models\Payment\SegpayPurchase;
use App\Models\Payment\TwokchargeTransactions;
use App\Observers\AppleObserver;
use App\Observers\BlockedObserver;
use App\Observers\EventMembershipObserver;
use App\Observers\EventObserver;
use App\Observers\FavouritesObserver;
use App\Observers\FlexpayObserver;
use App\Observers\GoogleObserver;
use App\Observers\PhotosObserver;
use App\Observers\SegpayObserver;
use App\Observers\TwokObserver;
use App\Observers\UserObserver;
use App\User;
use App\Services\BackendService;
use App\Services\HelperService;
use App\UserBlocked;
use App\UserFavorite;
use App\UserPhoto;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Facades\Image;
use DB;
use Log;
use App\Http\Middleware\TerminatingMiddleware;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /*DB::listen(function ($query) {
            Log::debug('Query', [
                $query->sql,
                $query->bindings,
                $query->time
            ]);
        });*/

        if(\App::environment() == 'prod') {
            \URL::forceScheme('https');
        }

        Validator::extend('imageable', function ($attribute, $value, $params, $validator) {
            try {
                $image = Image::make($value);

                return true;
            } catch (\Exception $e) {
                return false;
            }
        });

        $this->setupObservers();
        $this->setupDbListener();
    }

    private function setupObservers()
    {
        User::observe(UserObserver::class);
        UserBlocked::observe(BlockedObserver::class);
        UserFavorite::observe(FavouritesObserver::class);
        Event::observe(EventObserver::class);
        EventMembership::observe(EventMembershipObserver::class);
        UserPhoto::observe(PhotosObserver::class);

        /*
         * Payment observers
         */
        AppleSubscription::observe(AppleObserver::class);
        GoogleSubscription::observe(GoogleObserver::class);
        SegpayPurchase::observe(SegpayObserver::class);
        TwokchargeTransactions::observe(TwokObserver::class);
    }

    private function setupDbListener() {
        if (!config('query-logger.enabled')) {
            return;
        }

        \DB::listen(function ($queryExecuted) {
            $sql = $queryExecuted->sql;
            $bindings = $queryExecuted->bindings;
            $time = $queryExecuted->time;

            $logSqlQueriesSlowerThan = (float)config('query-logger.time-to-log', -1);

            if ($logSqlQueriesSlowerThan < 0 || $time < $logSqlQueriesSlowerThan) {
                return;
            }

            try {
                foreach ($bindings as $val) {
                    $sql = preg_replace('/\?/', "'{$val}'", $sql, 1);
                }

                \Log::debug('Query time - ' . $time . ' ms - ' . $sql);
            } catch (\Exception $e) {
                //  be quiet on error
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('backend-service', function(){
            return new BackendService();
        });

        $this->app->bind('helper-service', function(){
            return new HelperService();
        });

        $this->app->singleton(TerminatingMiddleware::class);
    }
}
