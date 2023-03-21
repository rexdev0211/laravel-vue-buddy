/* Types */
export const _type = {
    actions: {
        show: 'rush/requirements/show',
        hide: 'rush/requirements/hide',
    },
    mutations: {
        set:  'rush/requirements/set',
    },
    getters: {
        state: 'rush/requirements/state',
    },
}

/* Module */
export const _module = {
    state: {
        isShow:      false,
        withProIcon: false,
        type:        null,
        image:       '',
        title:       '',
        description: '',
        button:      '',
        imageStyle:  '',
    },
    actions: {
        /*
         * Show message
         */
        [_type.actions.show] ({commit}, data) {
            switch (data.type){
                case 'stars_and_strips':
                    data.isShow      = true
                    data.withProIcon = true
                    data.image       = '/images/splash/favorites.png'
                    data.imageStyle  = 'width: 52%'
                break;
            }

            commit(_type.mutations.set, data)
        },
        /*
         * Show message
         */
        [_type.actions.hide] ({commit}) {
            commit(_type.mutations.set, {})
        },
    },
    mutations: {
        /*
         * Set state
         */
        [_type.mutations.set] (state, data) {
            state.isShow      = data.isShow ? data.isShow : false
            state.withProIcon = data.withProIcon ? data.withProIcon : false
            state.type        = data.type ? data.type : null
            state.image       = data.image ? data.image : ''
            state.title       = data.title ? data.title : ''
            state.description = data.description ? data.description : ''
            state.button      = data.button ? data.button : ''
            state.imageStyle  = data.imageStyle ? data.imageStyle : ''
        },
    },
    getters: {
        /*
         * Get requirement state
         */
        [_type.getters.state] (state) {
            return state
        },
    },
}
