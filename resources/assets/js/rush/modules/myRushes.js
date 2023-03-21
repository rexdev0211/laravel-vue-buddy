/* Types */
export const _type = {
    actions: {
        set:    'rush/myRushes/set',
        add:    'rush/myRushes/add',
        edit:   'rush/myRusges/edit',
        delete: 'rush/myRushes/delete',
    },
    mutations: {
        add:    'rush/myRushes/add',
        update: 'rush/myRushes/update',
        edit:   'rush/myRushes/edit',
    },
    getters: {
        list: 'rush/myRushes/list',
    }
}

/* Module */
export const _module = {
    state: {
        list: [],
    },
    actions: {
        /*
         * Set My Rushes list
         */
        [_type.actions.set] ({commit}, data) {
            commit(_type.mutations.update, data)
        },
        /*
         * Add new Rush to My Rushes list
         */
        [_type.actions.add] ({commit}, data) {
            commit(_type.mutations.add, data)
        },
        /*
         * Edit Rush in My Rushes list
         */
        [_type.actions.edit] ({commit}, data) {
            commit(_type.mutations.edit, data)
        },
        /*
         * Delete Rush from My Rushes list
         */
        [_type.actions.delete] ({commit, state}, rushId) {
            commit(_type.mutations.update, state.list.filter(item => item.id != rushId))
        },
    },
    mutations: {
        /*
         * Update My Rushes list
         */
        [_type.mutations.add] (state, data) {
            state.list.unshift(data)
        },
        /*
         * Update My Rushes list
         */
        [_type.mutations.update] (state, data) {
            state.list = data
        },
        /*
         * Edit Rush in My Rushes list
         */
        [_type.mutations.edit] (state, data) {
            state.list = state.list.map(item => {
                if (item.id == data.id) {
                    return data
                } else {
                    return item
                }
            });
        },
    },
    getters: {
        /*
         * Get My Rushes list
         */
        [_type.getters.list] (state) {
            return state.list
        },
    },
}
