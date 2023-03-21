import axios from "axios";
import discoverModule from "./type";
import { storedFormFilters, loadLocalStorageValue } from "@discover/lib/helpers";
import {loadPersonalLocalStorageValue} from "../../lib/helpers";
import auth from '@general/lib/auth';

const actions = {
    [discoverModule.actions.users.set](store, payload) {
        store.commit(discoverModule.mutations.users.set, payload)
    },
    [discoverModule.actions.users.setVisible](store, payload) {
        store.commit(discoverModule.mutations.users.setVisible, payload)
    },
    [discoverModule.actions.users.push](store, payload) {
        store.commit(discoverModule.mutations.users.push, payload)
    },
    [discoverModule.actions.usersNextPageCount.push](store, payload) {
        store.commit(discoverModule.mutations.usersNextPageCount.push, payload);
    },
    [discoverModule.actions.users.remove](store, payload) {
        const index = store.state.usersAround.findIndex(e => e.id == payload.userId);
        let removedUser = false
        if (index !== -1) {
            removedUser = store.state.usersAround[index]
        }

        store.commit(discoverModule.mutations.users.remove, payload)
        return removedUser
    },
    [discoverModule.actions.users.reload](store) {
        if (false === window.pageLoaded) {
            return;
        }

        // Queue refresh
        store.dispatch(discoverModule.actions.users.setRefreshQueued, true)
        // Discover will be refreshed if it mounted
        console.log('[Poll] Reload users')
        app.$emit('reload-discover')
    },
    // loadUsersAround
    async [discoverModule.actions.users.load](store, payload) {
        // Cancel previous request
        if (!!app.discoverListCancelSource) {
            if (false === window.pageLoaded) {
                return;
            }

            app.discoverListCancelSource.cancel()
            app.discoverListCancelSource = null
        }

        let requestParams, allParams;

        if (!payload.enableFilter) {
            requestParams = store.getters[discoverModule.getters.filter.requestParams](true)
            allParams = {
                page: requestParams.page
            }

            if (payload.filterName) {
                allParams.filterName = payload.filterName;
            }

            if (requestParams.filterName) {
                allParams.filterName = requestParams.filterName;
            }
        } else {
            requestParams = store.getters[discoverModule.getters.filter.requestParams](true)
            allParams = {
                ...requestParams,
                ...payload,
            }
        }

        console.log('[Discover] Loading users...', allParams)

        let response = null
        let CancelToken = axios.CancelToken
        app.discoverListCancelSource = CancelToken.source()

        try {
            response = await axios.post('/api/getUsersAround', allParams, {
                cancelToken: app.discoverListCancelSource.token
            })
        } catch (thrown) {
            if (axios.isCancel(thrown)) {
                console.log(thrown.message)
            }
        }

        if (response && response.status === 200) {
            let data = response.data
            app.distance = data.distance

            //store.dispatch('addUsersAround', data.usersAround);
            store.dispatch(discoverModule.actions.users.push, data.usersAround.users)

            return data.usersAround.users.length
        }
    },
    // updateMyProfileInUsersAroundList
    [discoverModule.actions.users.update](store, payload) {
        store.commit(discoverModule.mutations.users.update, payload)
    },
    // saveDiscoverOption
    [discoverModule.actions.filter.set](store, { key, value, queueRefresh, refresh }) {
        // Do not set or handle undefined filter vars
        if (value === undefined) {
            return
        }

        // Do not update name filter at this case
        if (key === 'filterName' && !!value && value.length > 0 && value.length < 3) {
            return
        }

        store.commit(discoverModule.mutations.filter.set, { key, value })

        // Queue refresh, do not reload immediately
        // Waiting for app.$emit('reload-discover')
        if (!!queueRefresh) {
            store.dispatch(discoverModule.actions.users.setRefreshQueued, true)

        // Refresh list immediately
        } else if (!!refresh) {
            store.dispatch(discoverModule.actions.users.reload)
        }

        // Save value to local storage
        if (!(typeof value === 'string' || value instanceof String)){
            value = JSON.stringify(value)
        }

        if (key === 'filterType') {
            let type;
            switch (value) {
                case 'nearby':
                    type = 'All';
                    break;
                case 'recent':
                    type = 'New';
                    break;
                case 'favorites':
                    type = 'Favorites';
                    break;
                default:
                    break;
            }
            
            store.commit(discoverModule.mutations.filterBuddies.set, { type })
        }

        let userId = localStorage.getItem('userId');

        if (key !== 'filterName') {
            localStorage.setItem(`discover-`+ userId +`-${key}`, value)
        } else {
            localStorage.removeItem(`discover-`+ userId +`-${key}`);
        }
    },
    [discoverModule.actions.filter.remove](store, {key, queueRefresh, refresh}) {
        localStorage.removeItem(`discover-${key}`);

        let deactivatedFilter = key.replace('Values', '');

        localStorage.setItem(`discover-${deactivatedFilter}`, false);

        store.commit(discoverModule.mutations.filter.remove, { key })

        if (!!queueRefresh) {
            store.dispatch(discoverModule.actions.users.setRefreshQueued, true);
        } else if (!!refresh) {
            store.dispatch(discoverModule.actions.users.reload);
        }
    },
    [discoverModule.actions.filter.setPage](store, payload) {
        store.commit(discoverModule.mutations.filter.setPage, payload)
    },
    [discoverModule.actions.filter.loadFromLocalStorage]({ commit, getters }) {
        for (let key in storedFormFilters) {
            let localStorageValue = loadPersonalLocalStorageValue(key)
            let defaultValue = getters[discoverModule.getters.filter.default](key)
            if (auth.isAuthenticated() && key === 'filterOnline') {
                let userProfile = getters.userProfile
                if (userProfile && ['DE', 'CH', 'AT'].includes(userProfile.country_code)) {
                    // Set default filterOnline to true when user's country is DE, CH, AT.
                    defaultValue = true
                }
            }
            commit(discoverModule.mutations.filter.set, {
                key,
                value: (localStorageValue === null ? defaultValue : localStorageValue)
            })

            if (storedFormFilters[key].ranged) {
                let subkey = `${key}Values`
                localStorageValue = loadPersonalLocalStorageValue(subkey)
                defaultValue = getters[discoverModule.getters.filter.default](subkey)
                commit(discoverModule.mutations.filter.set, {
                    key: subkey,
                    value: (localStorageValue === null ? defaultValue : localStorageValue)
                })
            }
        }
    },
    [discoverModule.actions.users.setRefreshQueued](store, payload) {
        store.commit(discoverModule.mutations.users.setRefreshQueued, payload)
    },
}

export default actions