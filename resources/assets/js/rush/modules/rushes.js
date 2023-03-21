/* Types */
export const _type = {
    actions: {
        set:      'rush/rushes/set',
        position: 'rush/rushes/position',
    },
    mutations: {
        update:   'rush/rushes/update',
        position: 'rush/rushes/position',
    },
    getters: {
        position: 'rush/rushes/position',
        list:     'rush/rushes/list',
    }
}

/* Module */
export const _module = {
    state: {
        list:     [],
        position: 0,
    },
    actions: {
        /*
         * Set Rushes list
         */
        [_type.actions.set] ({commit}, data) {
            commit(_type.mutations.update, data)
            commit(_type.mutations.position, 0)
        },
        /*
         * Set Rush Main page latest scroll position
         */
        [_type.actions.position] ({commit}, position) {
            commit(_type.mutations.position, position)
        },
    },
    mutations: {
        /*
         * Update Rushes list
         */
        [_type.mutations.update] (state, data) {
            state.list = data
        },
        /*
         * Set Rush Main page latest scroll position
         */
        [_type.mutations.position] (state, position) {
            state.position = position
        },
    },
    getters: {
        /*
         * Get Rushes list
         */
        [_type.getters.list] (state) {
            return state.list
        },
        /*
         * Get Rush Main page latest position
         */
        [_type.getters.position] (state) {
            return state.position
        },
    },
}
