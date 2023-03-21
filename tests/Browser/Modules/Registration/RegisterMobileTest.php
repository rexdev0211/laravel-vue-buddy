<?php

namespace Tests\Browser\Modules\Notifications;

use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\Browser\Modules\BrowserTestCase;

class RegisterMobileTest extends BrowserTestCase
{
    /**
     * User visits other user (desktop)
     *
     * @return void
     * @throws \Throwable
     */
    public function testRegisterUserMobile()
    {
        $this->browse(function ($activeBrowser) {
            /**
             * @var Browser $activeBrowser
             */

            ################################################################
            # Resize
            ################################################################

            // iPhone X
            $activeBrowser->resize(375, 812)->refresh();

            ################################################################
            # Accept cookie policy
            # Cookie bar should hidden because it overlaps bottom navigation bar
            ################################################################

            $activeBrowser->visit(url('/'));
            if ($activeBrowser->resolver->find('@accept-cookie-policy')) {
                $activeBrowser
                    ->click('@accept-cookie-policy')
                    ->tap(function(){
                        $this->log('Cookie banned hidden');
                    });
            }

            ################################################################
            # Go to index page
            ################################################################

            $activeBrowser
                ->click('@registration-link')
                ->waitFor('@registration-form');

            ################################################################
            # Step 1: login + birth_date
            ################################################################

            $activeBrowser
                // Form is presented
                ->assertPresent('@registration-step-1')
                ->assertPresent('@registration-nickname')
                ->assertPresent('@registration-birth_date')

                // Fill the form
                ->type('@registration-nickname', Str::random(8))
                ->type('@registration-birth_date', '01.01.2000')
                ->click('@registration-next')

                // Verify no errors presented
                ->pause(1000)
                ->assertMissing('.form-error')
                ->assertMissing('.dialog-mode-error')
                ->assertPresent('@registration-step-2')

                ->tap(function(){
                    $this->log('Step 1 passed');
                });

            ################################################################
            # Step 2: email + password
            ################################################################

            $activeBrowser
                // Form is presented
                ->assertPresent('@registration-email')
                ->assertPresent('@registration-password')

                // Fill the form
                ->type('@registration-email', Str::random(8) . '@gmail.com')
                ->type('@registration-password', Str::random(8))
                ->click('@registration-next')

                // Verify no errors presented
                ->pause(1000)
                ->assertMissing('.form-error')
                ->assertMissing('.dialog-mode-error')
                ->assertPresent('@registration-step-3')

                ->tap(function(){
                    $this->log('Step 2 passed');
                });

            ################################################################
            # Step 3: photo
            ################################################################

            $activeBrowser
                // Form is presented
                ->assertPresent('@registration-photo-load')
                ->assertPresent('@registration-photo-file')

                ->click('@registration-photo-skip')

                // Verify no errors presented
                ->pause(1000)
                ->assertMissing('.form-error')
                ->assertMissing('.dialog-mode-error')
                ->assertPresent('@registration-step-4')

                ->tap(function(){
                    $this->log('Step 3 passed');
                });

            ################################################################
            # Step 4: location
            ################################################################

            $activeBrowser
                // Verify gps is unavailable
                ->assertMissing('@registration-location-gps')
                ->assertMissing('@registration-location-map')

                // Form is presented
                ->assertVisible('@registration-location-address')
                ->assertVisible('@registration-location-map_widget')
                ->assertVisible('@registration-next')

                ->typeSlowly('@registration-location-address', 'new york')
                ->pause(5000)
                ->click('@registration-next')

                // Verify no errors presented
                ->pause(1000)
                ->assertMissing('.form-error')
                ->assertMissing('.dialog-mode-error')
                ->assertPresent('@registration-step-5')

                ->tap(function(){
                    $this->log('Step 4 passed');
                });

            ################################################################
            # Step 5: location
            ################################################################

            $activeBrowser
                // Form is presented
                ->assertPresent('@registration-agreement')
                ->assertPresent('@registration-next')

                ->screenshot('agreement')
                ->assertVisible('@registration-next')
                ->click('@registration-agreement')
                ->click('@registration-next')

                // Verify no errors presented
                ->pause(1000)
                ->assertMissing('.form-error')
                ->assertMissing('.dialog-mode-error')

                ->waitFor('@registration-completed')
                ->tap(function(){
                    $this->log('Step 5 passed');
                });

            ################################################################
            # Welcome page
            ################################################################

            $activeBrowser
                // Form is presented
                ->assertPresent('@registration-photo-preview')
                ->assertPresent('@registration-go-to-discover')
                ->assertPresent('@registration-upgrade')

                ->check('@registration-go-to-discover')
                ->waitFor('.b-user__card')
                ->tap(function(){
                    $this->log('Welcome page passed');
                });
        });
    }
}
