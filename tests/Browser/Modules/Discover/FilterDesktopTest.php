<?php

namespace Tests\Browser\Modules\Discover;

use Laravel\Dusk\Browser;
use Tests\Browser\Modules\BrowserTestCase;
use Log;

class FilterDesktopTest extends BrowserTestCase
{
    /**
     * IMPORTANT!!!
     *
     * Before launching this test:
     * 1. Close local browser tabs OR logout from all the accounts. Otherwise, few tests will be failed.
     * 2. Verify that the newest front-end version was build
     * 3. Verify that all test users are active (status="active")
     * 4. Verify that all test users are very close to each other by lat and lng
     * 5. Active user:
     *    - is PRO
     * 6. Passive user:
     *    - have public photos
     *    - have no public videos
     * 7. Neutral user:
     *    - have public videos
     *    - have no public photos
     *    - should be always offline
     */

    /**
     * Discover behaviour (desktop)
     *
     * @return void
     * @throws \Throwable
     */
    public function testDiscoverDesktop()
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
            # Set passive user online
            ################################################################

            // Passive goes to discover in order to appear in discover
            $discoverUrl = url('/discover');
            $passiveUserFigure = "#discover-user-{$this->userPassive->id}";
            $neutralUserFigure = "#discover-user-{$this->userNeutral->id}";
            $enabledFilterSuffix = '.icon-Checkbox--filter.selected';

            // Passive goes to discover and see himself
            $passiveBrowser
                ->visit($discoverUrl)
                ->waitForLocation('/discover')
                ->waitFor($passiveUserFigure, 10)

                // Passive user should be online
                ->assertPresent("$passiveUserFigure .currently__online")
                ->tap(function(){
                    $this->log('Passive user see himself as online');
                });


            ################################################################
            # Initial filter state
            ################################################################

            $discoverEntriesCountPerPage = (int)config('const.LOAD_USERS_AROUND_LIMIT');

            // Load the first page
            $activeBrowser
                ->visit($discoverUrl)
                ->waitForLocation('/discover')
                ->waitFor('.b-user__card')

                // Verify filter state
                ->click('@discover-filters')
                ->assertVisible('@discover-filter-modal')
                ->assertPresent("#discover-filter-online > $enabledFilterSuffix")
                ->assertMissing("#discover-filter-pics > $enabledFilterSuffix")
                ->assertMissing("#discover-filter-videos > $enabledFilterSuffix")
                ->assertMissing("#discover-filter-age > $enabledFilterSuffix")
                ->assertMissing("#discover-filter-position > $enabledFilterSuffix")
                ->assertMissing("#discover-filter-height > $enabledFilterSuffix")
                ->assertMissing("#discover-filter-weight > $enabledFilterSuffix")
                ->assertMissing("#discover-filter-body > $enabledFilterSuffix")
                ->assertMissing("#discover-filter-penis > $enabledFilterSuffix")
                ->assertMissing("#discover-filter-chems > $enabledFilterSuffix")
                ->assertMissing("#discover-filter-status > $enabledFilterSuffix")
                ->tap(function(){
                    $this->log('Filter`s initial state is valid');
                })

                // Verify view switch
                ->assertVisible('#discover-filter-grid')
                ->assertVisible('#discover-filter-list')
                ->assertPresent('#discover-filter-grid.active')
                ->assertMissing('#discover-filter-list.active')

                // Switch to list mode
                ->click('#discover-filter-list')
                ->assertPresent('.b-user__list')
                ->assertMissing('.b-user__card')
                ->tap(function(){
                    $this->log('Switched to list mode');
                })

                // Switch to grid mode
                ->click('#discover-filter-grid')
                ->assertPresent('.b-user__card')
                ->assertMissing('.b-user__list')
                ->tap(function(){
                    $this->log('Switched to grid mode');
                })

                // Disable online filter
                ->click('#discover-filter-online')
                ->assertMissing("#discover-filter-online > $enabledFilterSuffix")
                ->tap(function(){
                    $this->log('Filter "online" was disabled');
                })
                ->click('@discover-filter-modal-close')
                ->waitFor('.b-user__card');


            ################################################################
            # Infinite loading test
            ################################################################

            $discoverEntriesCount = (int)$activeBrowser->driver->executeScript("return jQuery('.b-user__card').length;");
            $this->log("$discoverEntriesCount entries total");
            $this->assertEquals($discoverEntriesCountPerPage, $discoverEntriesCount);

            // Scroll down, load the second page
            $activeBrowser->script("jQuery('#js-discover-content').scrollTop(9999);");
            $activeBrowser->pause(3000);

            $discoverEntriesCount = (int)$activeBrowser->driver->executeScript("return jQuery('.b-user__card').length;");
            $this->log("$discoverEntriesCount entries total");
            $this->assertEquals($discoverEntriesCountPerPage * 2, $discoverEntriesCount);


            ################################################################
            # Scroll save test
            ################################################################

            // Scroll down, Load the third page
            $activeBrowser->script("jQuery('#js-discover-content').scrollTop(9999);");
            $activeBrowser->pause(3000);

            // Verify scrollTop value saved
            $savedScrollTop = (int)$activeBrowser->driver->executeScript("return jQuery('#js-discover-content').scrollTop();");
            $activeBrowser
                ->assertVisible('@section-discover')
                ->assertVisible('@section-events')

                // Go to events page
                ->click('@section-events')
                ->pause(3000)
                // Go back
                ->click('@section-discover')
                ->waitFor('.b-user__card');

            $currentScrollTop = (int)$activeBrowser->driver->executeScript("return jQuery('#js-discover-content').scrollTop();");

            // Verify scroll position saved and re-produced
            $this->assertEquals($savedScrollTop, $currentScrollTop);
            $this->log("Scroll successfully saved");

            // Move up
            $activeBrowser->script("jQuery('#js-discover-content').scrollTop(0);");


            ################################################################
            # "Online" filter
            ################################################################

            // Active user opens discover
            $activeBrowser
                ->assertUrlIs(url('/discover'))

                // Active user should see passive user in discover without any filters
                ->assertPresent($passiveUserFigure)
                ->assertSeeIn($passiveUserFigure, $this->userPassive->name)

                // Active user should see neutral user in discover without any filters
                ->assertPresent($neutralUserFigure)

                // Online filter should be enabled by default
                ->click('@discover-filters')
                ->assertVisible("#discover-filter-online")
                ->assertMissing("#discover-filter-online > $enabledFilterSuffix")
                ->click("#discover-filter-online")
                ->assertPresent("#discover-filter-online > $enabledFilterSuffix")
                ->click('@discover-filter-modal-close')
                ->tap(function(){
                    $this->log('Filter "online" was enabled');
                })
                ->waitFor('.b-user__card', 10)

                // Passive user should be online
                ->assertPresent($passiveUserFigure)
                ->assertPresent("$passiveUserFigure .currently__online")
                // Neutral user should be missing
                ->assertMissing($neutralUserFigure)

                ->tap(function(){
                    $this->log('Passive user was found via "online" filter');
                })

                // Disable "online" filter
                ->click('@discover-filters')
                ->assertVisible("#discover-filter-online")
                ->assertPresent("#discover-filter-online > $enabledFilterSuffix")
                ->click("#discover-filter-online")
                ->assertMissing("#discover-filter-online > $enabledFilterSuffix")
                ->tap(function(){
                    $this->log('Filter "online" was disabled');
                })
                ->click('@discover-filter-modal-close')
                ->waitFor('.b-user__card', 10);

            ################################################################
            # "Favourite" icon
            ################################################################

            // Make passive user favourite
            $activeBrowser->assertPresent($passiveUserFigure);

            if (!$activeBrowser->resolver->find("$passiveUserFigure .is-favorite")) {
                $this->log('Passive user is not in favourites list');

                $passiveUserModal = "@user-modal-{$this->userPassive->id}";
                $activeBrowser
                    // Active user visits passive user
                    ->click($passiveUserFigure)
                    ->waitFor($passiveUserModal, 10)
                    ->assertSeeIn($passiveUserModal, $this->userPassive->name)
                    ->assertSeeIn($passiveUserModal, '@not_favorite')

                    // Add passive user to favourites
                    ->click('@not_favorite')
                    ->waitFor("$passiveUserModal @is_favorite")
                    ->tap(function(){
                        $this->log('Added passive user to favourites');
                    })
                    ->click('@close-user-modal')

                    // Favourite icon should appear in discover list
                    ->assertPresent("$passiveUserFigure .is-favorite");
            }

            $activeBrowser
                ->refresh()
                ->waitFor("@favorite-online-{$this->userPassive->id}")
                ->tap(function(){
                    $this->log('Passive user present in onlineFavorites widget');
                });


            ################################################################
            # "Favourite" filter
            ################################################################

            $activeBrowser
                ->click('@discover-favorites')
                ->waitFor($passiveUserFigure, 10)
                ->assertPresent($passiveUserFigure)
                ->assertSeeIn($passiveUserFigure, $this->userPassive->name)
                ->assertMissing($neutralUserFigure)

                // Passive user should be in list
                ->tap(function(){
                    $this->log('Passive user was found via "favourite" filter');
                });


            ################################################################
            # "Pics" filter
            ################################################################

            $activeBrowser
                ->click('@discover-nearby')
                ->waitFor('.b-user__card', 10)
                ->assertPresent($passiveUserFigure)

                ->click('@discover-filters')
                ->assertVisible("#discover-filter-pics")

                // Enable "pics" filter
                ->assertMissing("#discover-filter-pics > $enabledFilterSuffix")
                ->click("#discover-filter-pics")
                ->assertPresent("#discover-filter-pics > $enabledFilterSuffix")

                ->tap(function(){
                    $this->log('Filter "pics" was enabled');
                })
                ->click("@discover-filter-modal-close")
                ->waitFor('.b-user__card', 10)

                // Active user should see passive user in discover
                ->assertSeeIn($passiveUserFigure, $this->userPassive->name)
                // Neutral user should be missing
                ->assertMissing($neutralUserFigure)

                ->tap(function(){
                    $this->log('Neutral user was found via "pics" filter');
                })

                // Disable "pics" filter
                ->click('@discover-filters')
                ->assertVisible("#discover-filter-pics")
                ->assertPresent("#discover-filter-pics > $enabledFilterSuffix")
                ->click("#discover-filter-pics")
                ->assertMissing("#discover-filter-pics > $enabledFilterSuffix")
                ->tap(function(){
                    $this->log('Filter "pics" was disabled');
                })
                ->click("@discover-filter-modal-close")
                ->waitFor('.b-user__card', 10);


            ################################################################
            # "Videos" filter
            ################################################################

            $activeBrowser
                ->click('@discover-filters')
                ->assertVisible("#discover-filter-videos")

                // Enable "videos" filter
                ->assertMissing("#discover-filter-videos > $enabledFilterSuffix")
                ->click("#discover-filter-videos")
                ->assertPresent("#discover-filter-videos > $enabledFilterSuffix")

                ->tap(function(){
                    $this->log('Filter "videos" was enabled');
                })
                ->click("@discover-filter-modal-close")
                ->waitFor('.b-user__card', 10)

                // Active user should see neutral user in discover
                ->assertPresent($neutralUserFigure)
                ->assertSeeIn($neutralUserFigure, $this->userNeutral->name)

                // Passive user should be missing
                ->assertMissing($passiveUserFigure)
                ->tap(function(){
                    $this->log('Passive user was found via "videos" filter');
                })

                // Disable "videos" filter
                ->click('@discover-filters')
                ->assertVisible("#discover-filter-videos")
                ->assertPresent("#discover-filter-videos > $enabledFilterSuffix")
                ->click("#discover-filter-videos")
                ->assertMissing("#discover-filter-videos > $enabledFilterSuffix")
                ->tap(function(){
                    $this->log('Filter "videos" was disabled');
                })
                ->click("@discover-filter-modal-close")
                ->waitFor('.b-user__card', 10);


            ################################################################
            # "Age" filter
            ################################################################

            $activeBrowser
                ->click('@discover-filters')
                ->assertVisible("#discover-filter-age")
                ->assertVisible("#discover-filter-age-value")

                // Enable "age" filter
                ->assertMissing("#discover-filter-age > $enabledFilterSuffix")
                ->click("#discover-filter-age")
                ->assertPresent("#discover-filter-age > $enabledFilterSuffix")

                ->click("#discover-filter-age-value")
                ->assertVisible("#filter-age-reveal")
                ->assertVisible("@apply-filter-age")
                ->assertVisible(".noUi-handle-lower")
                ->assertVisible(".noUi-handle-upper")

                // Set lowest age much more than 18
                ->dragRight('.noUi-handle-lower', 130)

                // Close modals
                ->click("@apply-filter-age")
                ->click("@discover-filter-modal-close");

            $activeBrowser
                ->tap(function(){
                    $this->log('Filter "age" was updated');
                })
                ->pause(3000)

                // All test users should be missing
                ->assertMissing($neutralUserFigure)
                ->assertMissing($passiveUserFigure)

                ->tap(function(){
                    $this->log('All test users are missing via "age" filter');
                })

                // Disable "age" filter
                ->click('@discover-filters')
                ->assertVisible("#discover-filter-age")
                ->assertPresent("#discover-filter-age > $enabledFilterSuffix")
                ->click("#discover-filter-age")
                ->assertMissing("#discover-filter-age > $enabledFilterSuffix")
                ->tap(function(){
                    $this->log('Filter "age" was disabled');
                })
                ->click("@discover-filter-modal-close")
                ->waitFor('.b-user__card', 10);


            ################################################################
            # "Position" filter
            ################################################################

            // Test "position" filter
            $activeBrowser
                ->click('@discover-filters')
                ->assertVisible("#discover-filter-position")
                ->assertVisible("#discover-filter-position-value")

                // Enable "age" filter
                ->assertMissing("#discover-filter-position > $enabledFilterSuffix")
                ->click("#discover-filter-position")
                ->assertPresent("#discover-filter-position > $enabledFilterSuffix")

                ->click("#discover-filter-position-value")
                ->assertVisible("#filter-position-reveal")
                ->assertVisible("@apply-filter-position")
                ->assertVisible("#form-positions-checkboxes")

                ->assertChecked("#form-checkbox-position-top")
                ->assertNotChecked("#form-checkbox-position-more_top")
                ->assertChecked("#form-checkbox-position-versatile")
                ->assertNotChecked("#form-checkbox-position-more_bottom")
                ->assertNotChecked("#form-checkbox-position-bottom")

                ->screenshot('position')
                ->click("#form-label-position-more_top")
                ->click("#form-label-position-more_bottom")
                ->click("#form-label-position-bottom")

                // Close modals
                ->click("@apply-filter-position")
                ->click("@discover-filter-modal-close");

            $activeBrowser
                ->tap(function(){
                    $this->log('Filter "position" was updated');
                })
                ->pause(3000)

                // All test users should be missing
                ->assertMissing($neutralUserFigure)
                ->assertMissing($passiveUserFigure)

                ->tap(function(){
                    $this->log('All test users are missing via "position" filter');
                })

                // Disable "position" filter
                ->click('@discover-filters')
                ->assertVisible("#discover-filter-position")
                ->assertPresent("#discover-filter-position > $enabledFilterSuffix")
                ->click("#discover-filter-position")
                ->assertMissing("#discover-filter-position > $enabledFilterSuffix")
                ->tap(function(){
                    $this->log('Filter "position" was disabled');
                })
                ->click("@discover-filter-modal-close")
                ->waitFor('.b-user__card', 10);

            ################################################################
            # No filters
            ################################################################

            $activeBrowser
                ->assertPresent($passiveUserFigure)
                ->assertPresent($passiveUserFigure)
                ->tap(function(){
                    $this->log('All test users present with no filters');
                });
        });
    }
}
