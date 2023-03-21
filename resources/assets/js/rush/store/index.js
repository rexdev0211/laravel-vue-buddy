import {
    _type as dialogType,
    _module as dialogModule
} from '../modules/dialog'

import {
    _type as announceType,
    _module as announceModule
} from '../modules/announce'

import {
    _type as userType,
    _module as userModule
} from '../modules/user'

import {
    _type as headerType,
    _module as headerModule
} from '../modules/header'

import {
    _module as requirementsModule
} from '../modules/requirements'

import {
    _type as sidebarType,
    _module as sidebarModule
} from '../modules/sidebar'

import {
    _type as rushesType,
    _module as rushesModule
} from '../modules/rushes'

import {
    _type as myRushesType,
    _module as myRushesModule
} from '../modules/myRushes'

const store = new Vuex.Store({
    modules: {
        dialogModule,
        announceModule,
        userModule,
        headerModule,
        sidebarModule,
        rushesModule,
        myRushesModule,
        requirementsModule,
    },
    strict:  typeof window.app.debug != 'undefined' ? window.app.debug : false,
    plugins: []
})

export default store