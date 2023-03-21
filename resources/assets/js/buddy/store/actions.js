import Vue from "vue";
import _ from "lodash";
import moment from 'moment';
import mixin from '@general/lib/mixin';
import auth from '@general/lib/auth'

import notificationsModule from '@notifications/module/store/type'
import discoverModule from "@discover/module/store/type";
import chatModule from "@chat/module/store/type";
import eventsModule from "@events/module/store/type";
import axios from "axios";

const actions = {
    showHealthAlert(store) {
        let lastAlertDate = localStorage.getItem('lastHealthAlertDate')
        if (
            lastAlertDate === null
            ||
            moment().diff(moment(lastAlertDate), 'hours') > 24
        ) {
            store.commit('setHealthAlert', true)
            localStorage.setItem('lastHealthAlertDate', moment().toISOString())
        }
    },
    async recoverPasswordStart(store, {email}) {
        let data = {
            email,
            lang: app.lang
        }

        let response = await axios.post('/api/password/email', data)
        if (response.status === 200) {
            return response.data
        }

        return false
    },
    async login(store, {username, password, remember}) {
        let data = {
            grant_type: 'password',
            client_id: 2,
            client_secret: 'nzVQUaVjdmYRVSbPRcosrv9emxvuxaEdQemoNZRi',
            username: username,
            password: password
        }

        let response = await axios.post('/oauth/token', data)
        if (response.status === 200) {
            let data = response.data
            let token = data.access_token

            auth._setAxiosAuthorizationHeader(token)

            let userLoaded = await store.dispatch('loadCurrentUserInfo')
            if (userLoaded) {
                let userId = store.state.profile.id
                if(!_.isUndefined(userId)) {
                    auth._rememberUser(token, userId, remember)

                    console.log('[Poll] User logged in. Profile loaded. Start polling.')
                    // store.dispatch('enableUserOnlineStatusPolling')
                    return true
                }
            }
        }

        return false
    },

    signalMessagesSeen(store) {
        let newValue = {
            has_new_messages: false
        }
        store.commit('updateUser', newValue)
        axios.post('/api/updateUser', newValue)
    },

    updateSidebarVisibility(store, payload){
        store.commit('updateSidebarVisibility', payload)
    },
    showDialog(store, settings){
        if (store.state.dialog.visible === true && settings.visible === true){
            store.dispatch('hideDialog')
        }
        store.commit('updateDialog', {...settings, visible: true })
    },
    hideDialog(store){
        store.commit('updateDialog', {
            mode: 'success',
            visible: false,
            callback: null,
            callbackNegative: null
        })
    },
    goToMyProfile() {
        app.goTo(app.lastProfilePage);
    },
    async shareVideos(store, videosIds) {
        try {
            let response = await axios.post('/api/share-videos', { videosIds })
            let sharingUrl = response.data.sharingUrl

            store.commit('setSharingUrl', sharingUrl);
        } catch (error) {
            console.log(error)
            return
        }
    },
    async inviteBuddies(store, data) {
        try {
            let response = await axios.post('/api/invite-members', data)

            // let sharingUrl = response.data.sharingUrl
            // store.commit('setSharingUrl', sharingUrl);
        } catch (error) {
            console.log(error)
            return
        }
    },
    async loadCurrentUserInfo(store, forceReload = false) { //can use argument destructuring here as {commit}
        console.log('loadCurrentUserInfo check state', auth._hasAxiosAuthorizationHeader(), _.isEmpty(store.state.profile.id), store, store.state.profile, forceReload)

        if (
            auth._hasAxiosAuthorizationHeader()
            &&
            (
                _.isEmpty(store.state.profile.id)
                ||
                forceReload
            )
        ) {
            let data = null

            // Send main request
            await axios.get('/api/currentUserInfo')
                .then( (res) => {
                    console.log('currentUserInfo request', res, res.status)
                    data = res.data
                    console.log('currentUserInfo response', data)
                }).catch((error) => {
                    console.error(error)
                    if ((error || error.request) && (error.status === 401 || Math.floor(error.status / 100) === 5)) {
                        app.logout()
                        store.commit('logout')
                        return
                    }
                });
            // IMPORTANT TO BE IMMEDIATELY HERE
            // Check user suspended and deleted status
            if (data.user.status === 'suspended' || !!data.user.deleted_at) {
                app.logout();
                store.commit('logout');
                return
            }

            // Check user deactivated status
            if (data.user.status === 'deactivated') {
                app.goTo('/profile/inactive')
            }

            // check if user language is set in db
            if (!data.user.language && app.lang) {
                data.user.language = app.lang;
                axios.post('/api/updateUser', {language: app.lang})
            }

            // Set app language from user profile
            if (data.user.language !== app.lang) {
                Vue.nextTick(function(){
                    app.setLanguage(data.user.language);
                })
            }

            // if (!data.user.is_guide_modal_shown) {
            //     app.$emit('show-parties-on', true)
            // }

            store.commit('setUser', data.user);
            store.commit('setOnlineFavorites', data.onlineFavorites);

			store.commit('setMyTaps', data.myTaps)
            store.commit('setProfileOptions', data.options)
            store.commit('setProfilePhotos', data.photos)
            store.commit('setProfileVideos', data.videos)
            store.commit('setMyEvents', data.myEvents)
            store.commit('setInvitationsToBang', data.invitationsToBang)
            store.commit('setInvitationsToClub', data.invitationsToClub)
            store.commit('setFavoritesCount', data.favoritesCount)
            store.commit('setBlockedCount', data.blockedCount)
            store.commit('setBlockedUsersIds', data.blockedUsersIds)
            store.commit('setUserIsPro', data.user.isPro)
            store.commit('setLatestWidget', data.latestWidget)
            store.commit('setDiscreetMode', data.user.discreet_mode)

            store.dispatch(eventsModule.actions.membership.set, data.membershipRequests)
            store.dispatch(eventsModule.actions.membership.setMy, data.activeMemberships)

            return true
        }

        return false
    },
    clearBlockedUsers(store) {
        store.commit('clearBlockedUsers')
    },
    async getBlockedUsers(store, { page, limit }) {
        try {
            let data = null;
            let response = await axios.post('/api/blockedUsers', { page, limit })

            if (response.status === 200) {
                data = response.data;
            } else if (error.status === 401){
                app.logout()
                store.commit('logout')
                return
            } else {
                return
            }

            store.commit('setBlockedUsers', data);

            return data;

        } catch (error) {
            console.log(error)
            return
        }
    },
    async unblockUser(store, { blockedUserId }) {
        try {
            let response = await axios.post('/api/unblockUser', { blockedUserId })

            if (response.status === 200) {
                store.commit('updateBlockedUsers', blockedUserId)
            } else if (error.status === 401) {
                app.logout()
                store.commit('logout')
                return
            } else {
                return
            }

        }  catch (error) {
            console.log(error)
            return
        }
    },
    // Poll func, every REFRESH_LAST_ACTIVE_SECONDS
    async updateUserStatus(store) {
        let ids = store.getters[chatModule.getters.conversations.userIds]
        let notificationsUserIds = store.getters[notificationsModule.getters.notifications.wave].map(e => e.user_from.id)
        ids.push(...notificationsUserIds)

        let response = await axios.post('/api/getUserStatus', { ids })
        if (response.status === 200) {
            let data = response.data

            // Update user
            if (!!data.user) {
                // Check user suspended and deleted status
                if (
                    data.user.status === 'suspended'
                    ||
                    !!data.user.deleted_at
                ) {
                    app.logout()
                    store.commit('logout')
                    return
                }
                store.commit('setUser', data.user)
            }

            // Update favourites status
            if (!!data.onlineFavorites) {
                store.commit('setOnlineFavorites', data.onlineFavorites);
            }

            // Update user entries
            if (!!data.freshUsers) {
                data.freshUsers.forEach((user) => {
                    let userId = user.id
                    if (
                        user.status === 'suspended'
                        ||
                        !!user.deleted_at
                    ) {
                        store.dispatch(notificationsModule.actions.remove, { userId })
                        return
                    }

                    store.commit(notificationsModule.mutations.update, {
                        userId,
                        fields: user
                    })

                    store.commit(chatModule.mutations.conversations.update, {
                        tracker: 'updateUserStatus',
                        userId,
                        interlocutor: user
                    })
                })
            }
        }
    },

    async updateDiscoverUserStatus(store) {
        let ids = store.getters[discoverModule.getters.usersAroundInvalidated]
        if (!ids.length) {
            // No invalidated visible users. Abort.
            return
        }

        let response = await axios.post('/api/getUserStatus', { ids })
        if (response.status === 200) {
            let data = response.data

            // Update user entries
            data.freshUsers.forEach((user) => {
                let userId = user.id
                if (
                    user.status === 'suspended'
                    ||
                    !!user.deleted_at
                ) {
                    store.dispatch(discoverModule.actions.users.remove, { userId })
                    return
                }

                store.commit(discoverModule.mutations.users.update, {
                    userId,
                    fields: {...user, loaded: moment() }
                })
            })
        }
    },

    // Poll func, every REFRESH_LAST_ACTIVE_SECONDS
    invalidateDiscoverLists(store) {
        let listLifeTime = window.REFRESH_USERS_AROUND_INTERVAL_SECONDS
        let usersListAge = moment().diff(app.lastUsersAroundRefresh, 'seconds', true)
        let eventListAge = moment().diff(app.lastEventsAroundRefresh, 'seconds', true)
        let debugInfo = {listLifeTime, usersListAge, eventListAge}

        console.log('[Poll] Trying to invalidate discover lists', debugInfo)

        if (usersListAge > listLifeTime) {
            // Invalidate users list
            store.dispatch(discoverModule.actions.users.setRefreshQueued, true)
            console.log('[Poll] Discover users list invalidated', debugInfo)
        }

        if (eventListAge > listLifeTime) {
            // Invalidate events list
            console.log('[Poll] Discover events list invalidated', debugInfo)
        }
    },

    launchPollIteration(store){
        // Update user status and all loaded users
        store.dispatch('updateUserStatus')

        // Users refresh will be queued if location changed
        store.dispatch('checkLocationChange')

        // Users refresh will be queued if discover list was invalidated
        // Events refresh will be queued if event list was invalidated
        store.dispatch('invalidateDiscoverLists')

        // Discover refresh will trigger only:
        // - If list was invalidated
        // - If location changed
        // DO NOT UNCOMMENT IT !!! DISCOVER SHOULD NOT BE AUTOMATICALLY UPDATED....
        // console.log('[Poll] Reset discover')
        // app.$emit('reload-discover')
    },

    // Enable poll
    enableUserOnlineStatusPolling(store) {
        if (auth.isAuthenticated()) {
            let intervalSeconds = window.REFRESH_LAST_ACTIVE_SECONDS
            console.log(`[Poll] Enabled, every ${intervalSeconds} sec.`)

            window.updateUserStatusInterval = setInterval(() => {
                console.log(`[Poll] Iteration started...`)
                store.dispatch('launchPollIteration')
            }, intervalSeconds * 1000)

            // Initial iteration
            store.dispatch('launchPollIteration')

            // Soft reload main lists
            store.dispatch('softReloadCachedLists')
        }
    },

    softReloadCachedLists(){
        if (!window.LAST_UPDATE) {
            window.LAST_UPDATE = moment()
        }

        // Mobile devices do not receive any notifications after ~1 min (lock state)
        // So we need to fire "soft reload" event. All main list will be invalidated.
        if (
            app.isMobile
            &&
            moment().diff(window.LAST_UPDATE, 'seconds', true) >= 60
        ) {
            console.log('[Poll] Reloading conversations, messages and notifications')

            app.$emit('invalidate-conversations')
            app.$emit('invalidate-messages')

            app.$emit('invalidate-notifications')
            app.$emit('invalidate-visitors')
            app.$emit('invalidate-visited')

            window.LAST_UPDATE = moment()
        } else {
            console.log('[Poll] Lists are still valid')
        }
    },

    // Disable polling
    disableUserOnlineStatusPolling() {
        clearInterval(window.updateUserStatusInterval)
        console.log(`[Poll] Disabled`)
    },

    async checkLocationChange({ state, dispatch, commit }){
        let updateFields = null;
        let lat, lng;
        let needToRefreshDiscover = false;

        if (state.profile.location_type === 'automatic') {
            commit('setLocationUpdating', true)
            try {
                ({lat, lng} = await mixin.methods.getCurrentPosition())
                if (lat && lng) {
                    let distance = mixin.methods.calculateDistanceBetween(lat, lng, state.profile.lat, state.profile.lng, 'm')

                    // Checking distance threshold for address
                    if (distance > window.REFRESH_ADDRESS_FOR_LAT_LNG_CHANGE_METERS) {
                        console.log('[Geo] Location changed. Updating user\'s profile.', {
                            current: distance,
                            limit: window.REFRESH_ADDRESS_FOR_LAT_LNG_CHANGE_METERS
                        })
                        try {
                            let address = await mixin.methods.getAddressForLatLng(lat, lng, true)
                            updateFields = {
                                lat,
                                lng,
                                address: address.formattedAddress,
                                locality: address.locality,
                                state: address.state,
                                country: address.country,
                                country_code: address.country_code,
                                address_lat: lat,
                                address_lng: lng
                            }

                            console.log('GPS profile');
                            console.log(state.profile);

                            if (state.profile.lat !== lat || state.profile.lng !== lng) {
                                console.log('needToRefreshDiscover set to true');
                                needToRefreshDiscover = true;
                            } else {
                                console.log('needToRefreshDiscover set to false');
                            }
                        } catch (e) {
                            console.log('[Geo] Location error.', e)
                        }
                    } else {
                        console.log('[Geo] Location didn\'t changed. User profile won`t be updated.')
                    }

                    // Checking distance threshold for users around list
                    // if (distance > window.DISTANCE_METERS_DIFFERENCE_RELOAD_USERS_AROUND) {
                    //     console.log(`[Geo] Location changed. Updating users around.`, {
                    //         current: distance,
                    //         limit: window.DISTANCE_METERS_DIFFERENCE_RELOAD_USERS_AROUND
                    //     })
                    //
                    //     // Queue refresh
                    //     dispatch(discoverModule.actions.users.reload, true)
                    // }
                }
            } catch (e) {
                updateFields = {
                    location_type: 'manual'
                }

                dispatch('showDialog', {
                    mode: 'confirm',
                    message: app.trans('fail_load_location'),
                    callback: () => { mixin.methods.goTo('/profile/location') }
                })

                console.log('[Geo] Geolocation detection mode set to "manual" because of error', { e })
            }

            if (updateFields != null) {
                commit('updateUser', updateFields)
                axios.post('/api/updateUser', updateFields)
            }

            commit('setLocationUpdating', false)
        } else {
            console.log('[Geo] Location wasn`t checked because it`s in manual mode')
        }

        if (needToRefreshDiscover) {
            console.log('Reload discover')
            dispatch(discoverModule.actions.users.reload);
            app.$emit('reload-events')
            app.$emit('reload-clubs')
        }
    },

    async forceUpdateLocation({ state, dispatch, commit }){
        let updateFields = null;
        let lat, lng;
        let needToRefreshDiscover = false;

        commit('setLocationUpdating', true)
        try {
            ({lat, lng} = await mixin.methods.getCurrentPosition())

            if (lat && lng) {
                try {
                    let address = await mixin.methods.getAddressForLatLng(lat, lng, true)
                    updateFields = {
                        lat,
                        lng,
                        address: address.formattedAddress,
                        locality: address.locality,
                        state: address.state,
                        country: address.country,
                        country_code: address.country_code,
                        address_lat: lat,
                        address_lng: lng
                    }

                    console.log('GPS profile');
                    console.log(state.profile);

                    if (state.profile.lat !== lat || state.profile.lng !== lng) {
                        console.log('needToRefreshDiscover set to true');
                        needToRefreshDiscover = true;
                    } else {
                        console.log('needToRefreshDiscover set to false');
                    }
                } catch (e) {
                    console.log('[Geo] Location error.', e)
                }

                // Checking distance threshold for users around list
                // let distance = mixin.methods.calculateDistanceBetween(lat, lng, state.profile.lat, state.profile.lng, 'm')
                // if (distance > window.DISTANCE_METERS_DIFFERENCE_RELOAD_USERS_AROUND) {
                //     console.log(`[Geo] Location changed. Updating users around.`, {
                //         current: distance,
                //         limit: window.DISTANCE_METERS_DIFFERENCE_RELOAD_USERS_AROUND
                //     })
                //
                //     // Queue refresh
                //     dispatch(discoverModule.actions.users.setRefreshQueued, true)
                // }
            }
        } catch (e) {
            updateFields = {
                location_type: 'manual'
            }

            dispatch('showDialog', {
                mode: 'error',
                message: app.trans('fail_auto_choose_manual_location')
            })

            console.log('[Geo] Geolocation detection mode set to "manual" because of error', { e })
        }

        if (updateFields != null) {
            commit('updateUser', updateFields)
            axios.post('/api/updateUser', updateFields)
        }
        commit('setLocationUpdating', false)

        if (needToRefreshDiscover) {
            console.log('Reload discover')
            setTimeout(function() {
                dispatch(discoverModule.actions.users.reload);
                app.$emit('reload-events')
                app.$emit('reload-clubs')
            }, 500);
        }
    },

    async loadUserInfo(context, userToken) {
        try {
            let response = await axios.get(`/api/userInfo/${userToken}`)
            if (response.status === 200) {
                let userId = response.data.id
                if (userId) {
                    Vue.set(context.state.usersInfo, userId, response.data)
                    return response.data
                } else {
                    Vue.set(context.state.usersInfo, userId, null)
                    return null
                }
            }
        } catch (error) {}
        return null
    },

    /**
     * try to switch discreet mode
     */
    trySwitchDiscreetMode(context) {
        if (context.state.userIsPro) {
            axios.post('/api/discreet-mode/change')
                .then(({data}) => {
                    if (data.success) {
                        context.dispatch('loadCurrentUserInfo', true)
                    } else {
                        mixin.methods.showErrorNotification(data.message)
                    }
                })
        } else {
            context.dispatch('requirementsAlertShow', 'mask')
        }
    },

    /**
     * Open profile edit
     */
    openProfileEdit(context) {
        context.commit('openProfileEdit')
    },

    /**
     * Close profile edit
     */
    closeProfileEdit(context) {
        context.commit('closeProfileEdit')
    },

    /**
     * Open Location menu from profile sidebar
     */
    openProfileLocation(context) {
        context.commit('openProfileLocation')
    },

    /**
     * Close Location menu from profile sidebar
     */
    closeProfileLocation(context) {
        context.commit('closeProfileLocation')
    },

    /**
     * Open Photos menu from profile sidebar
     */
    openProfilePhotos(context) {
        context.commit('openProfilePhotos')
    },

    /**
     * Close Photos menu from profile sidebar
     */
    closeProfilePhotos(context) {
        context.commit('closeProfilePhotos')
    },

    /**
     * Open Videos menu from profile sidebar
     */
    openProfileVideos(context) {
        context.commit('openProfileVideos')
    },

    /**
     * Close Videos menu from profile sidebar
     */
    closeProfileVideos(context) {
        context.commit('closeProfileVideos')
    },

    /**
     * Open Settings menu from profile sidebar
     */
    openProfileSettings(context) {
        context.commit('openProfileSettings')
    },

    /**
     * Close Settings menu from profile sidebar
     */
    closeProfileSettings(context) {
        context.commit('closeProfileSettings')
    },

    /**
     * Open Settings menu from profile sidebar
     */
    openProfileShare(context) {
        context.commit('openProfileShare')
    },

    /**
     * Close Settings menu from profile sidebar
     */
    closeProfileShare(context) {
        context.commit('closeProfileShare')
    },

    /**
     * Open Deactivation menu from profile settings
     */
    openProfileDeactivation(context) {
        context.commit('openProfileDeactivation')
    },

    /**
     * Open openCustomizeSharingLinks menu from profile settings
     */
    openCustomizeSharingLinks(context) {
        context.commit('openCustomizeSharingLinks')
    },

    /**
     * Open Delete All Sharing Links menu from profile settings
     */
    openDeleteAllSharingLinks(context) {
        context.commit('openDeleteAllSharingLinks')
    },

    /**
     * Close Deactivation menu from profile settings
     */
    closeProfileDeactivation(context) {
        context.commit('closeProfileDeactivation')
    },

    /**
     * Close Delete All Sharing Links menu from profile settings
     */
    closeDeleteAllSharingLinks(context) {
        context.commit('closeDeleteAllSharingLinks')
    },

    /**
     * Close CustomizeSharingLinks menu from profile settings
     */
    closeCustomizeSharingLinks(context) {
        context.commit('closeCustomizeSharingLinks')
    },

    /**
     * Open Help menu from profile sidebar
     */
    openProfileHelp(context) {
        context.commit('openProfileHelp')
    },

    /**
     * Close Help menu from profile sidebar
     */
    closeProfileHelp(context) {
        context.commit('closeProfileHelp')
    },
    /**
     * Close all profile pages
     */
    closeAllProfilePages(context) {
        context.commit('closeAllProfilePages')
    },

    /**
     * Redirect from requirements alert
     */
    requirementsAlertRedirect(context) {
        if ($('#chat-dropdown').is(':visible')) {
            $('#chat-dropdown').foundation('close')
        }
        if ($('#notif-dropdown').is(':visible')) {
            $('#notif-dropdown').foundation('close')
        }

        if (context.state.requirementsAlert.type == 'change_settings') {
            window.open('https://' + window.DOMAIN + '/profile/settings', '_system')
        } else {
            app.goTo('/profile/pro')
        }

        context.commit('hideRequirementsAlert')
    },
    /**
     * Show requirements alert
     */
    requirementsAlertShow(context, type) {
        context.commit('showRequirementsAlert', type);
    },
    /**
     * Hide requirements alert
     */
    requirementsAlertHide(context) {
        context.commit('hideRequirementsAlert');
    },
    /**
     * Show announce alert
     */
    announceAlertShow(context, data) {
        context.commit('showAnnounceAlert', data);
    },
    /**
     * Hide announce alert
     */
    announceAlertHide(context) {
        context.commit('hideAnnounceAlert');
    },
    /**
     * Redirect to Rush Widget
     */
    goToRush(context, isApp) {
        if (isApp) {
            if (!context.state.userIsPro){
                context.commit('showRequirementsAlert', 'censored');
                return;
            }

            if (context.state.profile.view_sensitive_media == 'no') {
                context.commit('showRequirementsAlert', 'change_settings');
                return;
            }
        }

        localStorage.setItem('widgetOpen', true)
        window.location = '/rush'
    },

    confirmDeleteSharingLinks() {
        axios.post('/api/user/delete-all-sharing-links')
    },
}

export default actions
