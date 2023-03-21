/* Types */
export const _type = {
    actions: {
        open: 'rush/sidebar/open',
        close: 'rush/sidebar/close',
    },
    mutations: {
        active: 'rush/sidebar/active',
    },
    getters: {
        active: 'rush/sidebar/active',
    }
}

/* Module */
export const _module = {
    state: {
        isSidebarActive: false,
    },
    actions: {
        /*
         * Open sidebar
         */
        [_type.actions.open] ({commit}) {
            commit(_type.mutations.active, true)
        },
        /*
         * Close sidebar
         */
        [_type.actions.close] ({commit}) {
            commit(_type.mutations.active, false)
        },
    },
    mutations: {
        /*
         * Change sidebar is active state
         */
        [_type.mutations.active] (state, isActive) {
            state.isSidebarActive = isActive
        },
    },
    getters: {
        /*
         * Get sidebar is active state
         */
        [_type.getters.active] (state) {
            return state.isSidebarActive
        },
    }
}
