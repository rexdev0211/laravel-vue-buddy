/* Types */
export const _type = {
    actions: {
        show: 'app/sidebar/show',
        hide: 'app/sidebar/hide',
    },
    mutations: {
        active: 'app/sidebar/active',
    },
    getters: {
        active: 'app/sidebar/active',
    },
}

/* Module */
export const _module = {
    state: {
        active: false,
    },
    actions: {
        /*
         * Show sidebar
         */
        [_type.actions.show] ({commit}) {
            commit(_type.mutations.active, true)
            commit('updateUser', { has_event_notifications: false })
        },
        /*
         * Hide sidebar
         */
        [_type.actions.hide] ({commit}) {
            commit(_type.mutations.active, false)
        },
    },
    mutations: {
        /*
         * Change active state
         */
        [_type.mutations.active] (state, is_active) {
            state.active = is_active
        },
    },
    getters: {
        /*
         * Get active state
         */
        [_type.getters.active] (state) {
            return state.active
        },
    },
}
