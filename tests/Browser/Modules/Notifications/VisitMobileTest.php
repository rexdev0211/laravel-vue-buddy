<?php

namespace Tests\Browser\Modules\Notifications;

use Laravel\Dusk\Browser;
use Tests\Browser\Modules\BrowserTestCase;
use Log;

class VisitMobileTest extends BrowserTestCase
{
    /**
     * User visits other user (desktop)
     *
     * @return void
     * @throws \Throwable
     */
    public function testVisitUserMobile()
    {
        $this->browse(function ($activeBrowser, $passiveBrowser) {
            /**
             * @var Browser $activeBrowser
             * @var Browser $passiveBrowser
             */

            ################################################################
            # Resize
            ################################################################

            // iPhone X
            $activeBrowser->resize(375, 812)->refresh();
            $passiveBrowser->resize(375, 812)->refresh();


            ################################################################
            # Log in users
            ################################################################

            // Log in active user
            $activeBrowser = $this->loginAs(
                '/log-in',
                self::USER_ACTIVE['login'],
                self::USER_ACTIVE['password'],
                $activeBrowser
            );

            // Log in passive user
            $passiveBrowser = $this->loginAs(
                '/log-in',
                self::USER_PASSIVE['login'],
                self::USER_PASSIVE['password'],
                $passiveBrowser
            );


            ################################################################
            # Accept cookie policy
            # Cookie bar should hidden because it overlaps bottom navigation bar
            ################################################################

            if ($activeBrowser->resolver->find('@accept-cookie-policy')) {
                $activeBrowser
                    ->click('@accept-cookie-policy')
                    ->tap(function(){
                        $this->log('Cookie banned hidden');
                    });
            }
            if ($passiveBrowser->resolver->find('@accept-cookie-policy')) {
                $passiveBrowser
                    ->click('@accept-cookie-policy')
                    ->tap(function(){
                        $this->log('Cookie banner hidden');
                    });
            }

            ################################################################
            # Create a visit
            ################################################################

            // Active user visits passive
            $this->createVisit($activeBrowser);


            ################################################################
            # Visited user checklist
            ################################################################

            $passiveBrowser
                ->assertUrlIs(url('/discover'))

                // Wait for a new visit entry and notification marker
                ->pause(3000)
                ->tap(function(){
                    $this->log('Check general marker enabled');
                })
                ->assertPresent('@notifications-marker')

                // General notifications marker should be gone after showing notifications page
                ->assertVisible('@notifications-page')
                ->click('@notifications-page')
                ->tap(function(){
                    $this->log('Check general marker disabled');
                })
                ->pause(1000)
                ->assertMissing('@notifications-marker')

                // New visitor marker should be active
                ->tap(function(){
                    $this->log('Check visitor marker enabled');
                })
                ->assertPresent('@new-visitor-marker')

                // New visitor marker should be gone after visitors page showed
                ->assertVisible('@visitors-tab')
                ->click('@visitors-tab')
                ->pause(1000)
                ->assertMissing('@new-visitor-marker')

                // Active user should be in the list of visitors
                ->waitFor('@visitor-' . $this->userActive->id, 10)
                // Check relative time
                ->with('@visitor-' . $this->userActive->id, function ($entry) {
                    $entry->assertSeeIn('.message__time', 'a few seconds ago');
                });


            ################################################################
            # Visitor checklist
            ################################################################

            $this->checkVisitedEntry($activeBrowser);

            ################################################################
            # Refresh the page and check entries / markers
            ################################################################

            // Refresh the page and check all the markers are gone
            $passiveBrowser
                ->refresh()
                ->visit('/notifications')

                // General notifications marker should be inactive
                ->assertMissing('@notifications-marker')

                // New visitor marker should be inactive
                ->assertMissing('@new-visitor-marker')

                // New visitor marker should be gone after visitors tab opened
                ->visit('/visitors')

                // Active user is in list of visitors
                ->waitFor('@visitor-' . $this->userActive->id, 10)
                // Check relative time
                ->with('@visitor-' . $this->userActive->id, function ($entry) {
                    $entry->assertSeeIn('.message__time', 'a few seconds ago');
                });

            // Refresh the page and check visited entry
            $activeBrowser->refresh();
            $this->checkVisitedEntry($activeBrowser);

            ################################################################
            # Refresh the page, open notifications page by
            # passive user and create a visit
            ################################################################

            $passiveBrowser
                ->refresh()
                ->visit('/notifications');

            // Create a visit
            $this->createVisit($activeBrowser);

            $passiveBrowser
                ->assertUrlIs(url('/notifications'))

                // Wait for a new visit entry
                ->pause(3000)

                // General notifications marker should be inactive,
                // because notifications page is showing
                ->assertMissing('@notifications-marker')

                // New visitor marker should be active
                ->assertVisible('@new-visitor-marker')

                // New visitor marker is gone after visitors page opened
                ->visit('/visitors')
                ->assertMissing('@new-visitor-marker')

                // Active user is in list of visitors
                ->waitFor('@visitor-' . $this->userActive->id, 10)
                // Check relative time
                ->with('@visitor-' . $this->userActive->id, function ($entry) {
                    $entry->assertSeeIn('.message__time', 'a few seconds ago');
                });

            // Create a visit
            $this->createVisit($activeBrowser);

            $passiveBrowser
                ->assertUrlIs(url('/visitors'))

                // Wait for a new visit entry
                ->pause(3000)

                // General notifications marker should be inactive, because
                // notifications page is open
                ->assertMissing('@notifications-marker')

                // New visitor marker should be inactive, because visitors page is open
                ->assertMissing('@new-visitor-marker')

                // Active user is in list of visitors
                ->assertVisible('@visitor-' . $this->userActive->id)
                // Check relative time
                ->with('@visitor-' . $this->userActive->id, function ($entry) {
                    $entry->assertSeeIn('.message__time', 'a few seconds ago');
                });
        });
    }

    protected function checkVisitedEntry(Browser $browser){
        return $browser
            ->visit('/visited')

            // Passive user is in list of visited
            ->waitFor('@visited-' . $this->userPassive->id, 10)
            // Check relative time
            ->with('@visited-' . $this->userPassive->id, function ($entry) {
                $entry->assertSeeIn('.message__time', 'a few seconds ago');
            });
    }

    protected function createVisit(Browser $activeBrowser): void
    {
        // Active user opens passive user's profile
        $activeBrowser
            ->visit('/user/' . $this->userPassive->id)
            ->pause(1000)
            ->tap(function(){
                $this->log("Visited user {$this->userPassive->name} (#{$this->userPassive->id})");
            });
    }
}
