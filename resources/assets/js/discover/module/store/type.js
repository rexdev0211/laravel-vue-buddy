const discoverModule = {
    actions: {
        removeUser: 'discover/actions/removeUser',
		users: {
            set: 'discover/actions/users/set',
            push: 'discover/actions/users/push',
            update: 'discover/actions/users/update',
            remove: 'discover/actions/users/remove',
            reload: 'discover/actions/users/reload',
            load: 'discover/actions/users/load',
            setRefreshQueued: 'discover/actions/users/setRefreshQueued',
            setVisible: 'discover/actions/users/setVisible'
        },
        usersNextPageCount: {
            push: 'discover/actions/usersNextPageCount/push'
        },
        filter: {
            setPage: 'discover/actions/filter/setPage',
            set: 'discover/actions/filter/set',
            update: 'discover/actions/filter/update',
            loadFromLocalStorage: 'discover/actions/filter/loadFromLocalStorage',
            remove: 'discover/actions/filter/remove'
        }
    },
    mutations: {
        users: {
            set: 'discover/mutations/users/set',
            push: 'discover/mutations/users/push',
            update: 'discover/mutations/users/update',
            remove: 'discover/mutations/users/remove',
            setRefreshQueued: 'discover/mutations/users/setRefreshQueued',
            setVisible: 'discover/mutations/users/setVisible'
        },
        usersNextPageCount: {
            push: 'discover/mutations/usersNextPageCount/push'
        },
        filter: {
            setPage: 'discover/mutations/filter/setPage',
            set: 'discover/mutations/filter/set',
            update: 'discover/actions/filter/update',
            remove: 'discover/actions/filter/remover'
        },
        filterBuddies: {
            set: 'discover/mutations/filterBuddies/set'
        }
    },
    getters: {
        usersAround: 'discover/getters/usersAround',
        filterBuddies: 'discover/getters/filterBuddies',
        usersNextPageCount: 'discover/getters/usersNextPageCount',
        usersAroundInvalidated: 'discover/getters/usersAroundInvalidated',
        filter: {
            get: 'discover/getters/filter/get',
            default: 'discover/getters/filter/default',
            defaultFilter: 'discover/getters/filter/defaultFilter',
            isDisabled: 'discover/getters/filter/isDisabled',
            countEnabled: 'discover/getters/filter/countEnabled',
            requestParams: 'discover/getters/filter/requestParams',
            proFilters: 'discover/getters/filter/proFilters'
        },
    }
}

export default discoverModule