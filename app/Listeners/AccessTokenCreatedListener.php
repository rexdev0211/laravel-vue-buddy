<?php namespace App\Listeners;

use App\Repositories\UserRepository;
use Carbon\Carbon;
use \Laravel\Passport\Events\AccessTokenCreated;

class AccessTokenCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(AccessTokenCreated $event)
    {
        $this->userRepository->update($event->userId, [
            'last_login' => Carbon::now()
//            'last_login' => \DB::raw('NOW()')
        ]);
    }
}