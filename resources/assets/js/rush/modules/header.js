/* Types */
export const _type = {
    actions: {
        streak: {
            set:   'rush/header/streak/set',
            clear: 'rush/header/strak/clear',
        },
        widget: {
            hide:    'rush/header/widget/hide',
            show:    'rush/header/widget/show',
        }
    },
    mutations: {
        streak: {
            set: 'rush/header/streak/set',
        },
        widget: {
            set:     'rush/header/widget/set',
            timeout: {
                set:   'rush/header/widget/timeout/set',
                clear: 'rush/header/widget/timeout/clear',
            },
        },
    },
    getters: {
        streak: 'rush/header/streak',
        widget: 'rush/header/widget',
    }
}

/* Module */
export const _module = {
    state: {
        widget:        false,
        streak:        null,
        widgetTimeout: null,
    },
    actions: {
        /*
         * Set header streak
         */
        [_type.actions.streak.set] ({commit}, streak) {
            commit(_type.mutations.streak.set, streak)
        },
        /*
         * Clear header streak
         */
        [_type.actions.streak.clear] ({commit}) {
            commit(_type.mutations.streak.set, null)
        },
        /*
         * Show header widget menu
         */
        [_type.actions.widget.show] ({commit}) {
            commit(_type.mutations.widget.set, true)
            commit(_type.mutations.widget.timeout.clear)
            commit(_type.mutations.widget.timeout.set, setTimeout(() => {
                commit(_type.mutations.widget.set, false)
            }, 3000))
        },
        /*
         * Hide header widget menu
         */
        [_type.actions.widget.hide] ({commit}) {
            commit(_type.mutations.widget.set, false)
            commit(_type.mutations.widget.timeout.clear)
        },
    },
    mutations: {
        /*
         * Change header streak value
         */
        [_type.mutations.streak.set] (state, streak) {
            state.streak = streak
        },
        /*
         * Change header wiget state
         */
        [_type.mutations.widget.set] (state, widget) {
            state.widget = widget
        },
        /*
         * Set header wiget hide timeout
         */
        [_type.mutations.widget.timeout.set] (state, widgetTimeout) {
            state.widgetTimeout = widgetTimeout
        },
        /*
         * Clear header wiget hide timeout
         */
        [_type.mutations.widget.timeout.clear] (state, widgetTimeout) {
            clearTimeout(state.widgetTimeout)
        },
    },
    getters: {
        /*
         * Get header streak value
         */
        [_type.getters.streak] (state) {
            return state.streak
        },
        /*
         * Get header widget state
         */
        [_type.getters.widget] (state) {
            return state.widget
        },
    }
}
