<?php

namespace Tests\Browser\Modules;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\User;
use Log;

class BrowserTestCase extends DuskTestCase
{
    const
        USER_ACTIVE = ['login' => 'uniwertz', 'password' => 365000],
        USER_PASSIVE = ['login' => 'uniwertz20', 'password' => 365000],
        USER_NEUTRAL = ['login' => 'uniwertz30', 'password' => 365000];

    protected $userActive;
    protected $userPassive;
    protected $userNeutral;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userActive = User::where('name', self::USER_ACTIVE['login'])->first();
        $this->userPassive = User::where('name', self::USER_PASSIVE['login'])->first();
        $this->userNeutral = User::where('name', self::USER_NEUTRAL['login'])->first();

        if (
            !empty($this->userActive)
            &&
            !empty($this->userPassive)
            &&
            !empty($this->userNeutral)
        ) {
            $this->log('All users were found and set up');
        } else {
            $this->log('Cannot find active or passive or neutral');
        }

        $this->log('Environment', ['env' => \App::environment()]);
    }

    protected function loginAs(string $url, string $login, string $password, Browser $browser) {
        return $browser
            ->visit($url)
            ->assertPresent('@login')
            ->assertPresent('@password')
            ->assertPresent('@submit-login-form')

            ->type('@login', $login)
            ->type('@password', $password)
            ->click('@submit-login-form')
            ->waitForLocation('/discover', 20)
            ->tap(function(){
                $this->log('Logged in');
            });
    }

    protected function log(string $message, array $payload = []){
        $className = last(explode('\\', get_called_class()));
        Log::debug("[$className] $message", $payload);
    }
}
