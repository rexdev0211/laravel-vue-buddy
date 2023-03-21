/* Types */
export const _type = {
    actions: {
        show:   'rush/dialog/show',
        hide: 'rush/dialog/hide',
    },
    mutations: {
        update:  'rush/dialog/update',
    },
    getters: {
        dialog: 'rush/dialog',
    },
}

/* Module */
export const _module = {
    state: {
        dialog: {
            mode: 'confirm',
            visible: false,
            message: 'Are you sure about that?',
            callback: null
        }
    },
    actions: {
        [_type.actions.show] ({ commit }, settings){
            //console.log('Action', _type.actions.show)
            commit(_type.mutations.update, {...settings, visible: true })
        },
        [_type.actions.hide] ({ commit }){
            //console.log('Action', _type.actions.hide)
            commit(_type.mutations.update, { mode: 'success', visible: false, callback: null })
        }
    },
    mutations: {
        [_type.mutations.update] (state, payload){
            // Prevent dialogs overlapping
            if (state.dialog.visible === true && payload.visible === true)
            return
            
            state.dialog = {...state.dialog, ...payload}
            
            //console.log('Mutation', _type.mutations.update, state.dialog)
        }
    },
    getters: {
        /*
        * Get dialog state
        */
        [_type.getters.dialog] (state) {
            //console.log('Getter', _type.getters.dialog, state.dialog)
            return state.dialog
        },
    },
}
