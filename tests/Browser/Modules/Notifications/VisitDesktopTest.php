<?php

namespace Tests\Browser\Modules\Notifications;

use Laravel\Dusk\Browser;
use Tests\Browser\Modules\BrowserTestCase;

class VisitDesktopTest extends BrowserTestCase
{
    /**
     * User visits other user (desktop)
     *
     * @return void
     * @throws \Throwable
     */
    public function testVisitUserDesktop()
    {
        $this->browse(function ($activeBrowser, $passiveBrowser) {
            /**
             * @var Browser $activeBrowser
             * @var Browser $passiveBrowser
             */

            ################################################################
            # Resize
            ################################################################

            // Desktop
            $activeBrowser->resize(1920, 1080);
            $passiveBrowser->resize(1920, 1080);

            ################################################################
            # Log in users
            ################################################################

            // Log in active user
            $activeBrowser = $this->loginAs(
                '/',
                self::USER_ACTIVE['login'],
                self::USER_ACTIVE['password'],
                $activeBrowser
            );

            // Log in passive user
            $passiveBrowser = $this->loginAs(
                '/',
                self::USER_PASSIVE['login'],
                self::USER_PASSIVE['password'],
                $passiveBrowser
            );

            ################################################################
            # Create a visit
            ################################################################

            // Passive goes to discover in order to appear in discover
            $discoverUrl = url('/discover');
            $passiveUserFigure = "#discover-user-{$this->userPassive->id}";
            $passiveBrowser
                ->visit($discoverUrl)
                ->waitForLocation('/discover')
                ->waitFor($passiveUserFigure, 10);

            // Active user visits passive
            $this->createVisit($activeBrowser);


            ################################################################
            # Visited user checklist
            ################################################################

            $passiveBrowser
                // Wait for a new visit entry and notification marker
                ->pause(3000)
                ->tap(function(){
                    $this->log('Check general marker enabled');
                })

                // General notifications marker should be active
                ->assertVisible('@notifications-marker')
                ->click('@notifications-dropdown-toggle')
                ->waitFor('@notifications-dropdown')

                // General notifications marker should be gone after notifications dropdown shown
                ->tap(function(){
                    $this->log('Check general marker disabled');
                })
                ->assertMissing('@notifications-marker')

                // New visitor marker should be active
                ->tap(function(){
                    $this->log('Check visitor marker enabled');
                })
                ->assertVisible('@new-visitor-marker')

                // New visitor marker should be gone after visitors tab opened
                ->assertVisible('@visitors-tab')
                ->click('@visitors-tab')
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

                // General notifications marker should be inactive
                ->assertMissing('@notifications-marker')
                ->click('@notifications-dropdown-toggle')
                ->waitFor('@notifications-dropdown')

                // New visitor marker should be inactive
                ->assertMissing('@new-visitor-marker')

                // New visitor marker should be gone after visitors tab opened
                ->assertVisible('@visitors-tab')
                ->click('@visitors-tab')

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
            # Refresh the page, open notifications dropdown by
            # passive user and create a visit
            ################################################################

            $passiveBrowser->refresh();

            // Open notification dropdown by passive user
            $passiveBrowser
                ->click('@notifications-dropdown-toggle')
                ->waitFor('@notifications-dropdown');

            // Create a visit
            $this->createVisit($activeBrowser);

            $passiveBrowser
                // Wait for a new visit entry
                ->pause(3000)

                // General notifications marker should be inactive, because dropdown is open
                ->assertMissing('@notifications-marker')

                // New visitor marker should be active
                ->assertVisible('@new-visitor-marker')

                // New visitor marker is gone after visitors tab opened
                ->assertVisible('@visitors-tab')
                ->click('@visitors-tab')

                // Active user is in list of visitors
                ->waitFor('@visitor-' . $this->userActive->id, 10)
                // Check relative time
                ->with('@visitor-' . $this->userActive->id, function ($entry) {
                    $entry->assertSeeIn('.message__time', 'a few seconds ago');
                });

            // Create a visit
            $this->createVisit($activeBrowser);

            $passiveBrowser
                // Wait for a new visit entry
                ->pause(3000)

                // General notifications marker should be inactive, because dropdown is open
                ->assertMissing('@notifications-marker')

                // New visitor marker should be inactive, because visitors tab is open
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
            ->click('@notifications-dropdown-toggle')
            ->waitFor('@notifications-dropdown')
            ->assertVisible('@visited-tab')
            ->click('@visited-tab')

            // Passive user is in list of visited
            ->waitFor('@visited-' . $this->userPassive->id, 10)
            // Check relative time
            ->with('@visited-' . $this->userPassive->id, function ($entry) {
                $entry->assertSeeIn('.message__time', 'a few seconds ago');
            });
    }

    protected function createVisit(Browser $activeBrowser): void
    {
        $discoverUrl = url('/discover');
        $passiveUserFigure = "#discover-user-{$this->userPassive->id}";
        $passiveUserModal = "@user-modal-{$this->userPassive->id}";

        // Active user opens passive user's profile
        $activeBrowser
            ->visit($discoverUrl)
            ->waitForLocation('/discover')

            // Active user should see passive user in discover
            ->waitFor($passiveUserFigure, 10)
            ->assertSeeIn($passiveUserFigure, $this->userPassive->name)

            // Active user visits passive user
            ->click($passiveUserFigure)
            ->waitFor($passiveUserModal, 10)
            ->assertSeeIn($passiveUserModal, $this->userPassive->name)

            ->click('@close-user-modal');
    }
}
