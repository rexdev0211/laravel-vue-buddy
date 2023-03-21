import chatModule from '@chat/module'
import notificationsModule from '@notifications/module'
import discoverModule from '@discover/module'
import eventsModule from '@events/module'
import clubsModule from '@clubs/module'

import {_module as sidebarModule} from '@general/modules/sidebar'

import mutations from "./mutations";
import actions from "./actions";
import getters from "./getters";

export const initialState = {
    // Custom success, error & confirm alert
    dialog: {
        mode: 'confirm',
        visible: false,
        message: 'Hello! Are you sure `bout that?',
        callback: null,
        callbackNegative: null,
        submitText: 'yes',
        rejectText: 'no'
    },

    // Modal windows
    modal: {
        // Visible single user modal
        user: null,
        register: false
    },

    sidebar: {
        filter: {
            visible: false,
        },
        profile: {
            visible: false,
        },
    },

    // Loaded entities cache
    usersInfo: {},
    eventsInfo: {},
    invitationsToBang: {},
    clubsInfo: {},
    invitationsToClub: {},

    myEvents: [],
    myClubs: [],
	myTaps: [],
	
    profile: {},
    profileOptions: {},
    profilePhotos: [],
    profileVideos: [],
    profileBlockedUsers: [],
    userIsPro: null,
    discreetMode: false,

    profileEditOpened: false,
    profileLocationOpened: false,
    profilePhotosOpened: false,
    profileVideosOpened: false,
    profileSettingsOpened: false,
    profileShareOpened: false,
    profileDeactivationOpened: false,
    customizeSharingLinksOpened: false,
    deleteAllSharingLinksOpened: false,
    profileHelpOpened: false,

    sharingUrl: '',

    latestWidget: null,
    supportUser: null,
    prepUser: null,
    onlineFavorites: [],
    favoritesCount: 0,
    blockedCount: 0,
    blockedUsersIds: [],
    haveUnblockedUsers: false,
	
    requirementsAlert: {
        isShow: false,
        withProIcon: false,
        type: null,
        image: '',
        title: '',
        description: '',
        button: '',
        imageStyle: '',
    },
    announceAlert: {
        isShow:  false,
        message: null,
    },

	isTapped: false,
    healthAlert: false,
    locationUpdating: false
}

const store = new Vuex.Store({
    modules: {
        chatModule,
        eventsModule,
        clubsModule,
        sidebarModule,
        notificationsModule,
        discoverModule,
    },
    state: initialState,
    mutations,
    actions,
    getters
});

store.subscribeAction((action, state) => {
    console.log(`[Action] ${action.type}`, { payload: action.payload });
});

store.subscribe((mutation, state) => {
    console.log(`[Mutation] ${mutation.type}`, { payload: mutation.payload });
});

export default store