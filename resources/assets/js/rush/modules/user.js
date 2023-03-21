/* Types */
export const _type = {
    actions: {
        profile: {
            set: 'rush/user/profile/set',
        },
        images: {
            set: 'rush/user/images/set',
        },
        notifications: {
            set: 'rush/user/notifications/set',
        },
        rush: {
            queue: {
                set:       'rush/user/rush/queue/set',
                favorites: 'rush/user/rush/queue/favorites',
            },
            favorites: {
                set: 'rush/user/rush/favorites/set',
            },
            images: {
                set: 'rush/user/rush/images/set',
                add: 'rush/user/rush/images/add',
            },
        },
    },
    mutations: {
        profile:  {
            update: 'rush/user/profile/update',
        },
        images:  {
            update: 'rush/user/images/update',
        },
        notifications: {
            update: 'rush/user/notifications/update',
        },
        rush: {
            queue: {
                update:    'rush/user/rush/queue/update',
                favorites: 'rush/user/rush/queue/favorites',
            },
            favorites: {
                update: 'rush/user/rush/favorites/update',
            },
            images: {
                update:  'rush/user/rush/images/update',
                unshift: 'rush/user/rush/images/unshift',
            },
        },
    },
    getters: {
        profile:       'rush/user/profile',
        images:        'rush/user/images',
        notifications: 'rush/user/notifications',
        rush: {
            queue:     'rush/user/rush/queue',
            favorites: 'rush/user/rush/favorites',
            images:    'rush/user/rush/images',
        },
    },
}

/* Module */
export const _module = {
    state: {
        profile: null,
        images:  [],
        rush:   {
            queue:     [],
            favorites: [],
            images:    [],
        },
        notifications: {
            messages:      false,
            notifications: false,
        },
    },
    actions: {
        /*
         * Set user profile
         */
        [_type.actions.profile.set] ({commit}, data) {
            commit(_type.mutations.profile.update, data)
        },
        /*
         * Set user images
         */
        [_type.actions.images.set] ({commit}, data) {
            commit(_type.mutations.images.update, data)
        },
        /*
         * Set user notifications
         */
        [_type.actions.notifications.set] ({commit}, data) {
            commit(_type.mutations.notifications.update, data)
        },
        /*
         * Set user rush images
         */
        [_type.actions.rush.images.set] ({commit}, data) {
            commit(_type.mutations.rush.images.update, data)
        },
        /*
         * Add user rush image to list
         */
        [_type.actions.rush.images.add] ({commit}, data) {
            commit(_type.mutations.rush.images.unshift, data)
        },
        /*
         * Set user favorite rushes
         */
        [_type.actions.rush.favorites.set] ({commit}, data) {
            commit(_type.mutations.rush.favorites.update, data)
        },
        /*
         * Set user rushes queue
         */
        [_type.actions.rush.queue.set] ({commit}, data) {
            commit(_type.mutations.rush.queue.update, data)
        },
        /*
         * Set user favorites queue
         */
        [_type.actions.rush.queue.favorites] ({commit}, data) {
            commit(_type.mutations.rush.queue.favorites, data)
        },
    },
    mutations: {
        /*
         * Update user profile list
         */
        [_type.mutations.profile.update] (state, data) {
            state.profile = data
        },
        /*
         * Update user images list
         */
        [_type.mutations.images.update] (state, data) {
            state.images = data
        },
        /*
         * Update user notifications state
         */
        [_type.mutations.notifications.update] (state, data) {
            if (typeof data.messages !== 'undefined')      state.notifications.messages      = data.messages ? true : false
            if (typeof data.notifications !== 'undefined') state.notifications.notifications = data.notifications ? true : false
        },
        /*
         * Update user rush images list
         */
        [_type.mutations.rush.images.update] (state, data) {
            state.rush.images = data
        },
        /*
         * Prepend user rush images list
         */
        [_type.mutations.rush.images.unshift] (state, data) {
            state.rush.images.unshift(data)
        },
        /*
         * Update user favorite rushes list
         */
        [_type.mutations.rush.favorites.update] (state, data) {
            state.rush.favorites = data
        },
        /*
         * Update user rushes queue list
         */
        [_type.mutations.rush.queue.update] (state, data) {
            state.rush.queue = data
        },
        /*
         * Update user favorites queue list
         */
        [_type.mutations.rush.queue.favorites] (state, data) {
            state.rush.queue.favorites = data
        },
    },
    getters: {
        /*
         * Get user profile
         */
        [_type.getters.profile] (state) {
            return state.profile
        },
        /*
         * Get user images
         */
        [_type.getters.images] (state) {
            return state.images
        },
        /*
         * Get user notifications
         */
        [_type.getters.notifications] (state) {
            return state.notifications
        },
        /*
         * Get user rush images
         */
        [_type.getters.rush.images] (state) {
            return state.rush.images
        },
        /*
         * Get user favorite rushes
         */
        [_type.getters.rush.favorites] (state) {
            return state.rush.favorites
        },
        /*
         * Get user rushes queue
         */
        [_type.getters.rush.queue] (state) {
            return state.rush.queue
        },
    },
}
