/* Types */
export const _type = {
    actions: {
        show:   'rush/announce/show',
        hide:   'rush/announce/hide',
        latest: 'rush/announce/latest',
    },
    mutations: {
        update: 'rush/announce/update',
        isShow: 'rush/announce/isShow',
    },
    getters: {
        announce: 'rush/announce',
    },
}

/* Module */
export const _module = {
    state: {
        isShow: false,
        latest: null,
        type:   null,
    },
    actions: {
        /*
         * Show announce popup
         */
        [_type.actions.show] ({commit}, data) {
            commit(_type.mutations.update, data)
            commit(_type.mutations.isShow, true)
        },
        /*
         * Set latest announce data
         */
        [_type.actions.latest] ({commit}, data) {
            commit(_type.mutations.update, data)
        },
        /*
         * Show announce popup
         */
        [_type.actions.hide] ({commit}, data) {
            commit(_type.mutations.isShow, false)
        },
    },
    mutations: {
        /*
         * Set Announce data
         */
        [_type.mutations.update] (state, data) {
            state.type   = data.type ?? null
            state.latest = data.latest ?? null
        },
        /*
         * Set isShow
         */
        [_type.mutations.isShow] (state, isShow) {
            state.isShow = isShow
        },
    },
    getters: {
        /*
         * Get notifications
         */
        [_type.getters.announce] (state) {
            return state
        },
    },
}
