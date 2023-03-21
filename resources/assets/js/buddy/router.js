import VueRouter from 'vue-router';
import auth from '@general/lib/auth';

let routes = [
    {
        path: '/hustlaball',
        redirect: '/page/hustlaball'
    },
    {
        path: '/',
        name: 'home',
        components: {
            app: require('./views/new/'+(typeof window.googleLanding === 'undefined' ? 'Home.vue' : 'HomeGoogle.vue')).default,
            mobile: require('./views/new/'+(typeof window.googleLanding === 'undefined' ? 'Home.vue' : 'HomeGoogle.vue')).default,
            desktop: require('./views/new/'+(typeof window.googleLanding === 'undefined' ? 'Home.vue' : 'HomeGoogle.vue')).default
        }
    },
    {
        path: '/discover',
        name: 'discover',
        components: {
            app: require('@discover/views/mobile/Discover.vue').default,
            mobile: require('@discover/views/mobile/Discover.vue').default,
            desktop: require('@discover/views/desktop/Discover.vue').default,
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/filters',
        name: 'filters',
        components: {
            mobile: require('@discover/views/mobile/Filters.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/search',
        name: 'search',
        components: {
            app: require('@search/views/mobile/SearchBuddy.vue').default,
            mobile: require('@search/views/mobile/SearchBuddy.vue').default,
            desktop: require('@search/views/desktop/SearchBuddy.vue').default,
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/events',
        name: 'events',
        components: {
            app: require('@events/views/mobile/List.vue').default,
            mobile: require('@events/views/mobile/List.vue').default,
            desktop: require('@events/views/desktop/List.vue').default,
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/create-event',
        name: 'create-event',
        component: require('@events/views/mobile/event/Create.vue').default,
        meta: {requiresAuth: true}
    },
    {
        path: '/create-bang',
        name: 'create-bang',
        component: require('@events/views/mobile/bang/Create.vue').default,
        meta: {requiresAuth: true}
    },
    {
        path: '/event/:eventId',
        name: 'event',
        component: require('@events/views/mobile/event/Item.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/bang/:eventId',
        name: 'bang',
        component: require('@events/views/mobile/bang/Item.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/event/:eventId/photo/:photoId',
        name: 'event-photo',
        component: require('@events/views/mobile/event/Photo.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/event/:eventId/video/:videoId',
        name: 'event-video',
        component: require('@events/views/mobile/event/Video.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/edit-event/:eventId',
        name: 'edit-event',
        component: require('@events/views/mobile/event/Edit.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/edit-bang/:eventId',
        name: 'edit-bang',
        component: require('@events/views/mobile/event/Edit.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/clubs',
        name: 'clubs',
        components: {
            app: require('@clubs/views/mobile/List.vue').default,
            mobile: require('@clubs/views/mobile/List.vue').default,
            desktop: require('@clubs/views/desktop/List.vue').default,
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/create-club',
        name: 'create-club',
        component: require('@clubs/views/mobile/Create.vue').default,
        meta: {requiresAuth: true}
    },
    {
        path: '/edit-club/:clubId',
        name: 'edit-club',
        component: require('@clubs/views/mobile/Edit.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/club/:clubId',
        name: 'club',
        component: require('@clubs/views/mobile/Item.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/chat/event-user/:eventId/:userToken',
        name: 'chat-event-user',
        component: require('@chat/views/mobile/ChatEventUser.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/chat-event-user-photo/:eventId/:userToken/:msgId',
        name: 'chat-event-user-photo',
        component: require('@chat/views/mobile/ChatEventUserPhoto.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/chat-event-user-video/:eventId/:userToken/:msgId',
        name: 'chat-event-user-video',
        component: require('@chat/views/mobile/ChatEventUserVideo.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/chat/group/:eventId',
        name: 'chat-group',
        component: require('@chat/views/mobile/ChatGroup.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/chat-group-photo/:eventId/:msgId',
        name: 'chat-group-photo',
        component: require('@chat/views/mobile/ChatGroupPhoto.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/chat-group-video/:eventId/:msgId',
        name: 'chat-group-video',
        component: require('@chat/views/mobile/ChatGroupVideo.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/notifications',
        name: 'notifications',
        components: {
            app: require('@notifications/views/mobile/Notifications.vue').default,
            mobile: require('@notifications/views/mobile/Notifications.vue').default,
            desktop: require('@discover/views/desktop/Discover.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/visitors',
        name: 'visitors',
        components: {
            app: require('@notifications/views/mobile/Visitors.vue').default,
            mobile: require('@notifications/views/mobile/Visitors.vue').default,
            desktop: require('@discover/views/desktop/Discover.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/visited',
        name: 'visited',
        components: {
            app: require('@notifications/views/mobile/Visited.vue').default,
            mobile: require('@notifications/views/mobile/Visited.vue').default,
            desktop: require('@discover/views/desktop/Discover.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/chat',
        name: 'chat',
        components: {
            app: require('@chat/views/mobile/Conversations.vue').default,
            mobile: require('@chat/views/mobile/Conversations.vue').default,
            desktop: require('@chat/views/desktop/Conversations.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/chat/unread',
        name: 'chat-unread',
        components: {
            app: require('@chat/views/mobile/Conversations.vue').default,
            mobile: require('@chat/views/mobile/Conversations.vue').default,
            desktop: require('@chat/views/desktop/Conversations.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/chat/favorites',
        name: 'chat-favorites',
        components: {
            app: require('@chat/views/mobile/Conversations.vue').default,
            mobile: require('@chat/views/mobile/Conversations.vue').default,
            desktop: require('@chat/views/desktop/Conversations.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/location',
        name: 'profileLocation',
        components: {
            app: require('@profile/views/mobile/page/ProfileLocation.vue').default,
            mobile: require('@profile/views/mobile/page/ProfileLocation.vue').default,
            desktop: require('@profile/views/desktop/page/ProfileLocation.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/info',
        name: 'profileInfo',
        components: {
            app: require('@profile/views/mobile/page/ProfileInfo.vue').default,
            mobile: require('@profile/views/mobile/page/ProfileInfo.vue').default,
            desktop: require('@profile/views/desktop/page/ProfileInfo.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/photos',
        name: 'profilePhotos',
        components: {
            app: require('@profile/views/mobile/page/ProfilePhotos.vue').default,
            mobile: require('@profile/views/mobile/page/ProfilePhotos.vue').default,
            desktop: require('@profile/views/desktop/page/ProfilePhotos.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/videos',
        name: 'profileVideos',
        components: {
            app: require('@profile/views/mobile/page/ProfileVideos.vue').default,
            mobile: require('@profile/views/mobile/page/ProfileVideos.vue').default,
            desktop: require('@profile/views/desktop/page/ProfileVideos.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/edit',
        name: 'profileEdit',
        components: {
            app: require('@profile/views/mobile/page/ProfileEdit.vue').default,
            mobile: require('@profile/views/mobile/page/ProfileEdit.vue').default,
            desktop: require('@profile/views/desktop/page/ProfileEdit.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/settings',
        name: 'profileSettings',
        components: {
            app: require('@profile/views/mobile/page/ProfileSettings.vue').default,
            mobile: require('@profile/views/mobile/page/ProfileSettings.vue').default,
            desktop: require('@profile/views/desktop/page/ProfileSettings.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/share',
        name: 'profileShare',
        components: {
            app: require('@profile/views/mobile/page/ProfileShare.vue').default,
            mobile: require('@profile/views/mobile/page/ProfileShare.vue').default,
            desktop: require('@profile/views/desktop/page/ProfileShare.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/help',
        name: 'profileHelp',
        components: {
            app: require('@profile/views/mobile/page/ProfileHelp.vue').default,
            mobile: require('@profile/views/mobile/page/ProfileHelp.vue').default,
            desktop: require('@profile/views/desktop/page/ProfileHelp.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/pro',
        name: 'profileSubscription',
        components: {
            app: require('@profile/views/mobile/page/ProfileSubscription.vue').default,
            mobile: require('@profile/views/mobile/page/ProfileSubscription.vue').default,
            desktop: require('@profile/views/desktop/page/ProfileSubscription.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/pro/failed',
        name: 'profileSubscriptionError',
        components: {
            app: require('@profile/views/mobile/page/ProfileSubscriptionError.vue').default,
            mobile: require('@profile/views/mobile/page/ProfileSubscriptionError.vue').default,
            desktop: require('@profile/views/desktop/page/ProfileSubscriptionError.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/deactivate',
        name: 'profileDeactivate',
        components: {
            app: require('@profile/views/mobile/page/ProfileDeactivate.vue').default,
            mobile: require('@profile/views/mobile/page/ProfileDeactivate.vue').default,
            desktop: require('@profile/views/desktop/page/ProfileDeactivate.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/delete_all_sharing_links',
        name: 'deleteAllSharingLinks',
        components: {
            app: require('@profile/views/mobile/page/DeleteAllSharingLinks.vue').default,
            mobile: require('@profile/views/mobile/page/DeleteAllSharingLinks.vue').default,
            desktop: require('@profile/views/desktop/page/DeleteAllSharingLinks.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/customize-sharing-links',
        name: 'customizeSharingLinks',
        components: {
            app: require('@profile/views/mobile/page/CustomizeSharingLinks.vue').default,
            mobile: require('@profile/views/mobile/page/CustomizeSharingLinks.vue').default,
            desktop: require('@profile/views/desktop/page/CustomizeSharingLinks.vue').default
        },
        meta: {requiresAuth: true}
    },
    {
        path: '/profile/inactive',
        name: 'profileInactive',
        component: require('@profile/views/mobile/page/ProfileInactive.vue').default,
        meta: {requiresAuth: true}
    },

    {
        path: '/profile/photo/:photoId',
        name: 'profilePhoto',
        component: require('@profile/views/mobile/page/media/ProfilePhoto.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/profile/video/:videoId',
        name: 'profileVideo',
        component: require('@profile/views/mobile/page/media/ProfileVideo.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/profile/unblock-users',
        name: 'unblock-users',
        component: require('@profile/views/mobile/page/ProfileUnblockUsers.vue').default,
        meta: {requiresAuth: true},
        props: true
    },

    {
        path: '/recover-password',
        name: 'recover-password',
        component: require('./views/mobile/RecoverPassword.vue').default
    },
    {
        path: '/reset-password/:token',
        name: 'reset-password',
        component: require('./views/mobile/ResetPassword.vue').default,
        props: true
    },
    {
        path: '/log-in',
        name: 'login',
        components: {
            app: require('./views/mobile/Login.vue').default,
            mobile: require('./views/mobile/Login.vue').default,
            desktop: require('./views/mobile/Login.vue').default
        }
    },
    {
        path: '/welcome',
        name: 'welcome',
        meta: {requiresAuth: true},
        components: {
            app: require('./views/widgets/registration/Welcome.vue').default,
            mobile: require('./views/widgets/registration/Welcome.vue').default,
            desktop: require('./views/widgets/registration/Welcome.vue').default
        }
    },
    {
        path: '/register',
        name: 'register',
        components: {
            app: require('./views/mobile/Register.vue').default,
            mobile: require('./views/mobile/Register.vue').default,
            desktop: require('./views/desktop/Register.vue').default
        }
    },
    {
        path: '/user/:userToken',
        name: 'user',
        component: require('./views/mobile/user/User.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/user/:userToken/photo/:photoId',
        name: 'user-photo',
        component: require('./views/mobile/user/UserPhoto.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/user/:userToken/video/:videoId',
        name: 'user-video',
        component: require('./views/mobile/user/UserVideo.vue').default,
        meta: {requiresAuth: true},
        props: true
    },

    {
        path: '/chat/:userToken',
        name: 'chat-user',
        component: require('@chat/views/mobile/ChatUser.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/chat-photo/:userToken/:msgId',
        name: 'chat-photo',
        component: require('@chat/views/mobile/ChatPhoto.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/chat-video/:userToken/:msgId',
        name: 'chat-video',
        component: require('@chat/views/mobile/ChatVideo.vue').default,
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/page/cancel',
        name: 'page-cancel',
        components: {
            app: require('@profile/views/mobile/page/ProfileCancel.vue').default,
            mobile: require('@profile/views/mobile/page/ProfileCancel.vue').default,
            desktop: require('@profile/views/desktop/page/ProfileCancel.vue').default
        },
        meta: {requiresAuth: true},
        props: true
    },
    {
        path: '/page/:slug',
        name: 'page-view',
        component: require('@buddy/views/mobile/GooglePage.vue').default,
        props: true
    },
    {
        path: '/content-page/:slug', // google static page
        name: 'content-page-view',
        component: require('@buddy/views/mobile/GooglePage.vue').default,
        props: true
    },

    // {path: '*', redirect: '/'}
];

const scrollBehavior = (to, from, savedPosition) => {
    //app.log(to, from, savedPosition);

    if (savedPosition === null) { //not pushed back button (browser or programmatically)
        setTimeout(() => {
            app.pushRouteHistory(from.fullPath);
        }, 0)
    } else {
        setTimeout(() => {
            app.routesHistory.pop();
        }, 0)
    }

    //get back in chat list to where user was before entering the chat-user page
    if (to.name == 'chat-user' && ['chat', 'chat-unread', 'chat-favorites'].includes(from.name)) {
        app.prevChatPosition = document.querySelector('#application-wrapper').scrollTop
    }
    if (['chat', 'chat-unread', 'chat-favorites'].includes(to.name) && from.name == 'chat-user' && app.prevChatPosition) {
        tryGoToChatPosition(app.prevChatPosition)
    }

    if (['chat', 'chat-unread', 'chat-favorites'].includes(to.name) && app.prevChatPosition) {
        tryGoToChatPosition(app.prevChatPosition)
    }

    //get back in chat list to where user was before entering the chat-user page
    if (to.name == 'chat-event-user' && from.name == 'chat-events') {
        app.prevEventChatPosition = document.querySelector('#application-wrapper').scrollTop
    }
    if (to.name == 'chat-events' && from.name == 'chat-event-user' && app.prevEventChatPosition) {
        tryGoToChatPosition(app.prevEventChatPosition)
    }

    //get back in users around list to where user was before entering the user profile page
    if (to.name == 'discover' && app.prevUsersAroundPosition) {
        tryGoToUsersAroundPosition(app.prevUsersAroundPosition)
    }

    if (to.name == 'events' && app.prevEventsAroundPosition) {
        tryGoToEventsAroundPosition(app.prevEventsAroundPosition)
    }

    //get back in events around list to where user was before entering the event profile page
    if (to.name == 'events' && from.name == 'event' && app.prevEventsAroundPosition) {
        tryGoToEventsAroundPosition(app.prevEventsAroundPosition)
    }

    if (to.name === 'events' && from.name === 'bang' && app.prevEventsAroundPosition) {
        tryGoToEventsAroundPosition(app.prevEventsAroundPosition)
    }

    //get back in notifications list to where user was before entering the user profile page
    if (to.name == 'user' && from.name == 'notifications') {
        app.prevNotificationsPosition = document.querySelector('#js-notifications-content').scrollTop
    }
    if (to.name == 'notifications' && from.name == 'user' && app.prevNotificationsPosition) {
        tryGoToNotificationsPosition(app.prevNotificationsPosition)
    }

    //get back in visitors list to where user was before entering the user profile page
    if (to.name == 'user' && from.name == 'visitors') {
        app.prevVisitorsPosition = document.querySelector('#js-visitors-content').scrollTop
    }
    if (to.name == 'visitors' && from.name == 'user' && app.prevVisitorsPosition) {
        tryGoToVisitorsPosition(app.prevVisitorsPosition)
    }

    //get back in visited list to where user was before entering the user profile page
    if (to.name == 'user' && from.name == 'visited') {
        app.prevVisitedPosition = document.querySelector('#js-visited-content').scrollTop
    }
    if (to.name == 'visited' && from.name == 'user' && app.prevVisitedPosition) {
        tryGoToVisitedPosition(app.prevVisitedPosition)
    }

    //get back to chat-user position when returning from chat-photo page
    if (to.name == 'chat-photo' && from.name == 'chat-user') {
        app.prevUserChatPosition = document.querySelector('#js-chat-user-cmp').scrollTop
    }

    //get back to chat-user position when returning from chat-vide page
    if (to.name == 'chat-video' && from.name == 'chat-user') {
        app.prevUserChatPosition = document.querySelector('#js-chat-user-cmp').scrollTop
    }

    //same for events
    if (to.name == 'chat-event-user-photo' && from.name == 'chat-event-user') {
        app.prevUserChatPosition = document.querySelector('#js-chat-group-cmp').scrollTop
    }

    //and videos
    if (to.name == 'chat-event-user-video' && from.name == 'chat-event-user') {
        app.prevUserChatPosition = document.querySelector('#js-chat-group-cmp').scrollTop
    }

    //if browser's back button was pressed when going from photo to user profile: null !== savedPosition //null !== savedPosition &&
    if (to.name == 'user' && from.name == 'user-photo') {
        tryGoToHash('#photo' + from.params.photoId);
        return {};
    }

    //if browser's back button was pressed when going from video to user profile: null !== savedPosition //null !== savedPosition &&
    if (to.name == 'user' && from.name == 'user-video') {
        tryGoToHash('#video' + from.params.videoId);
        return {};
    }

    //if hash is present in url: go there
    if (to.hash) {
        tryGoToHash(to.hash);
        return {}
    }

    // if the returned position is falsy or an empty object, will retain current scroll position.
    return {}
};

//TODO: change iteration to scrollHeight verification
const tryGoToUsersAroundPosition = function (position, iteration = 1) {
    if (iteration++ > 10) {
        return;
    }
    const hash = document.querySelector('#content-wrapper-wrapper');
    if (null !== hash) {
        hash.classList.add('disable-scroll');
        hash.scrollTop = position;
        hash.classList.remove('disable-scroll');
    } else {
        setTimeout(() => {
            tryGoToUsersAroundPosition(position, iteration);
        }, 50);
    }
}

const tryGoToEventsAroundPosition = function (position, iteration = 1) {
    if (iteration++ > 10) {
        return;
    }
    const hash = document.querySelector('#application-wrapper');
    if (null !== hash) {
        hash.scrollTop = position;
    } else {
        setTimeout(() => {
            tryGoToEventsAroundPosition(position, iteration);
        }, 50);
    }
}

const tryGoToNotificationsPosition = function (position, iteration = 1) {
    if (iteration++ > 10) {
        return;
    }
    const hash = document.querySelector('#js-notifications-content');
    if (null !== hash) {
        hash.scrollTop = position;
    } else {
        setTimeout(() => {
            tryGoToNotificationsPosition(position, iteration);
        }, 50);
    }
}

const tryGoToVisitorsPosition = function (position, iteration = 1) {
    if (iteration++ > 10) {
        return;
    }
    const hash = document.querySelector('#js-visitors-content');
    if (null !== hash) {
        hash.scrollTop = position;
    } else {
        setTimeout(() => {
            tryGoToVisitorsPosition(position, iteration);
        }, 50);
    }
}

const tryGoToVisitedPosition = function (position, iteration = 1) {
    if (iteration++ > 10) {
        return;
    }
    const hash = document.querySelector('#js-visited-content');
    if (null !== hash) {
        hash.scrollTop = position;
    } else {
        setTimeout(() => {
            tryGoToVisitedPosition(position, iteration);
        }, 50);
    }
}

//not used this one
const tryGoToUserChatPosition = function (position, iteration = 1) {
    if (iteration++ > 10) {
        return;
    }
    const hash = document.querySelector('#application-wrapper');
    if (null !== hash) {
        hash.scrollTop = position;
    } else {
        setTimeout(() => {
            tryGoToUserChatPosition(position, iteration);
        }, 50);
    }
}

const tryGoToChatPosition = function (position, iteration = 1) {
    if (iteration++ > 10) {
        return;
    }
    const hash = document.querySelector('#application-wrapper');
    if (null !== hash) {
        hash.scrollTop = position;
    } else {
        setTimeout(() => {
            tryGoToChatPosition(position, iteration);
        }, 50);
    }
}

const tryGoToHash = function (hashValue, iteration = 1) {
    if (iteration++ > 10) {
        return;
    }
    let hash = document.querySelector(hashValue);
    if (null !== hash) {
        hash.scrollIntoView();
    } else {
        setTimeout(() => {
            tryGoToHash(hashValue, iteration);
        }, 50);
    }
}

const router = new VueRouter({
    mode: 'history',
    base: __dirname,
    scrollBehavior,
    routes
});

router.beforeEach((to, from, next) => {
    if (app.isMobile) {
        let routesOrder = ['home', 'recover-password', 'reset-password', 'register', 'login', 'discover', 'filters', 'search', 'user', 'user-photo', 'user-video',
        'events', 'create-event', 'event', 'edit-event', 'event-photo', 'event-video', 'chat', 'chat-unread', 'chat-favorites', 'chat-user',
        'chat-events', 'chat-event-user', 'chat-event-user-photo', 'chat-event-user-video', 'chat-photo', 'chat-video', 'notifications', 'visitors', 'visited',
        'profile', 'profileInfo', 'profileStats', 'profilePhotos', 'profilePhoto', 'profileVideos', 'profileVideo', 'profileSettings', 'profileShare',
        'profileHelp', 'profileDeactivate', 'profileInactive', 'profileSubscription', 'page-view'];

        app.transitionName = 'fade';
    }

    //remember back page
    to.meta.back = from.fullPath;

    //redirect root to discover
    if (to.name == 'home' && auth.isAuthenticated()) {
        location.pathname = '/discover'
    } else if (['register', 'reset-password', 'recover-password'].includes(to.name) && auth.isAuthenticated()) {
        location.pathname = '/discover'
    } else if (to.name == 'profileInactive' && app.$store && app.$store.state.profile.status == 'active') {
        next({path: '/discover'})
    } else if (to.name != 'profileInactive' && app.$store && app.$store.state.profile.status == 'deactivated') {
        next({path: '/profile/inactive'})

    //check if need to redirect after login
    } else if (from.name == 'login' && auth.isAuthenticated() && sessionStorage.getItem('redirectOnLogin')) {
        const redirectTo = sessionStorage.getItem('redirectOnLogin')
        sessionStorage.removeItem('redirectOnLogin')
        //console.log('router.beforeEach', '#5')
        next({path: redirectTo})

    //check if route requires auth
    } else if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!auth.isAuthenticated()) {
            // next({path: '/', query: {redirect: to.fullPath}});
            sessionStorage.setItem('redirectOnLogin', to.fullPath)
            //console.log('router.beforeEach', '#6')
            next({path: '/'})
        } else {
            //console.log('router.beforeEach', '#7')
            next();
        }
    } else {
        //console.log('router.beforeEach', '#8')
        next();
    }
});

export default router;
