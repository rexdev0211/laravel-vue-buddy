import './lib/bootstrap'

import store from './store'
import router from './router'
import { mapGetters, mapActions } from 'vuex'
import Announce from './views/widgets/Announce.vue'
import RushHeader from './views/widgets/RushHeader.vue'
import RushSidebar from './views/widgets/RushSidebar.vue'
import Requirement from './views/widgets/Requirement.vue'
import Dialog from './views/widgets/Dialog.vue'

import {listener} from './lib/echoListener'
import {getUserLanguage} from '@general/lib/lang/helpers'
import i18n from '@general/lib/lang/init'

import {
    _type as dialogType,
    _module as dialogModule
} from '@rush/modules/dialog'

import {
    _type as announceType,
    _module as announceModule
} from '@rush/modules/announce'

import {
    _type as userType,
    _module as userModule
} from '@rush/modules/user'

import {
    _type as headerType,
    _module as headerModule
} from '@rush/modules/header'

import {
    _type as sidebarType,
    _module as sidebarModule
} from '@rush/modules/sidebar'

import {
    _type as rushesType,
    _module as rushesModule
} from '@rush/modules/rushes'

import {
    _type as myRushesType,
    _module as myRushesModule
} from '@rush/modules/myRushes'

const rush = new Vue({
    i18n,
    el: '#app',
    store,
    router,
    mixins: [],
    data: {
        lang: getUserLanguage(),
        isApp: location.hostname == window.app.appDomain,
        tapped: false,
    },
    mediaQueries: window.mediaQueries,
    components: {
        announce:       Announce,
        requirement:    Requirement,
        'rush-header':  RushHeader,
        'rush-sidebar': RushSidebar,
        'modal-dialog': Dialog,
    },
    computed: {
        ...mapGetters({
            myRushes:           myRushesType.getters.list,
            userProfile:        userType.getters.profile,
            isSidebarActive:    sidebarType.getters.active,
        }),
        headerAllowed() {
            return this.$route.name != 'rush.view' && this.$route.name != 'rush.favorite'
        },
    },
    methods: {
        ...mapActions({
            setUserProfile:        userType.actions.profile.set,
            setUserImages:         userType.actions.images.set,
            setUserNotifications:  userType.actions.notifications.set,
            setRushImages:         userType.actions.rush.images.set,
            setRushFavorites:      userType.actions.rush.favorites.set,
            setRushQueue:          userType.actions.rush.queue.set,
            setRushes:             rushesType.actions.set,
            setMyRushes:           myRushesType.actions.set,
            closeSidebar:          sidebarType.actions.close,
            showDialog:            dialogType.actions.show,
            hideWidget:            headerType.actions.widget.hide,
            showAnnounce:          announceType.actions.show,
            prepareAnnounce:       announceType.actions.latest,
        }),
        userTapped() {
            this.tapped = true
        },
        getRushData() {
            let v = this

            axios.get('/api/rush').then(({data}) => {
                if (this.isApp) {
                    if (!data.me.isPro || data.me.view_sensitive_media == 'no') {
                        window.location = '/'
                        return;
                    }
                }

                if (data.widgetAnnounced == 'announced') {
                    v.showAnnounce({
                        type:   'welcome',
                        latest: 'rush.welcome',
                    })
                } else if (data.widgetAnnounced == 'rush.welcome') {
                    v.prepareAnnounce({
                        latest: 'rush.welcome',
                    })
                }

                v.setRushes(data.rushes)
                v.setMyRushes(data.myRushes)
                v.setUserImages(data.userImages)
                v.setRushImages(data.rushImages)
                v.setUserProfile(data.me)
                v.setRushFavorites(data.favorites)
                v.setRushQueue(data.queue)
                v.setUserNotifications({
                    messages:      data.me.has_unseen_messages,
                    notifications: data.me.has_unseen_notifications,
                })
            })
            .catch((error) => {
                console.log(error)
            });
        },
        closeOverlays(){
            this.closeSidebar()
            this.hideWidget()
        },
        beep() {
            if (!this.userProfile.notification_sound) {
                return
            }

            if (!this.tapped) {
                return
            }

            let snd = new Audio("/sounds/notification.mp3");
            snd.play();
        },
    },
    mounted() {
        let v = this

        this.getRushData()
        this.$nextTick(() => {
            listener()
        });

        axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response && error.response.status === 401) {
                    v.logout()
                } else if (error.response.status == 422) {
                    // debugger;
                    let data = error.response.data;

                    if (typeof data == 'string') {
                        this.showAlertNotification({
                            type: 'error',
                            message: data, // this.trans(data)
                        })
                    } else {
                        let keys = Object.keys(data.errors)
                        if (keys.length) {
                            for (let i in keys) { //object
                                for (let e in data.errors[keys[i]]) { //array
                                    this.showAlertNotification({
                                        type: 'error',
                                        message: data.errors[keys[i]][e], // this.trans(data.errors[keys[i]][e])
                                    })
                                }
                            }
                        } else {
                            console.error('422 keys are empty')
                        }
                    }
                }
                else if (error.response.status == 500) {
                    this.showAlertNotification({
                        type: 'error',
                        message:  "Error 500 has occurred, we're already working on fixing it",
                    })
                }
                else {
                    this.showAlertNotification({
                        type: 'error',
                        message: "An error has occurred, we're already working on fixing it",
                    })
                }

                return Promise.reject(error)
            }
        )
    },
})

window.rush = rush
