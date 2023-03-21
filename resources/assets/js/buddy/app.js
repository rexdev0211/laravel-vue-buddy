
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './lib/bootstrap';
import router from './router';

import BootstrapVue from 'bootstrap-vue';
Vue.use(BootstrapVue);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import store from './store';
import CookiesBar from './views/widgets/CookiesBar.vue';
import OfflineNotification from './views/widgets/OfflineNotification.vue';
import ModalUser from './views/desktop/ModalUser.vue';
import ModalUserMobile from './views/desktop/ModalUserMobile.vue';
import RegisterModal from "./views/desktop/RegisterModal.vue";
import RecoverPasswordModal from "./views/desktop/RecoverPasswordModal.vue";
import ModalEvent from '@events/views/desktop/event/Modal.vue';
import ModalBang from '@events/views/desktop/bang/Modal.vue';
import ModalChat from '@chat/views/desktop/ModalChat.vue';
import ModalMobileChat from '@chat/views/mobile/ModalMobileChat.vue';
import ModalChatEvent from '@chat/views/desktop/ModalChatEvent.vue';
import ModalChatGroup from '@chat/views/desktop/ModalChatGroup.vue';
import ModalChatPreview from "@chat/views/widgets/ModalChatPreview.vue";
import HealthAlert from './views/widgets/HealthAlert.vue';
import Alert from './views/widgets/Alert.vue';
import Dialog from './views/widgets/Dialog.vue';
import Preloader from './views/widgets/Preloader.vue';
import Footer from './views/new/widgets/Footer.vue';
import GoogleFooter from './views/new/widgets/GoogleFooter.vue';

import {initEchoListeners} from './lib/echoListener';

window.store = store;

import i18n from '@general/lib/lang/init';
import { getUserLanguage, setUserLanguage } from '@general/lib/lang/helpers';

import discoverModule from "@discover/module/store/type";
import eventsModule from "@events/module/store/type";
import Vue from "vue";
import {VueReCaptcha} from "vue-recaptcha-v3";

if (window.APP_ENV == 'local') {
    Vue.config.debug = true;
    Vue.config.devtools = true;
}

//TODO: send this to server for logging
Vue.config.errorHandler = function(err, vm, info) {
    console.error(err, vm, info);
};

Vue.use(VueReCaptcha, {
    siteKey: window.RECAPTCHA_SITE_KEY,
})

window.pageLoaded = false;

const app = new Vue({
    i18n,
    router,
    store,
    data: {
        isApplicationLoading: true,
        lang: getUserLanguage(),
        loading: false,
        distance: window.PRE_SEARCH_USERS_AROUND_KM,
        routesHistory: [],
        lastChatPage: '/chat',
        lastProfilePage: '/profile/edit',
        lastUsersAroundRefresh: moment(),
        lastEventsAroundRefresh: moment(),
    },
    mixins: [
        require('@general/lib/mixin').default,
    ],
    components: {
        'footer-widget': Footer,
        'google-footer-widget': GoogleFooter,
        'preloader': Preloader,
        'cookies-bar': CookiesBar,
        'offline-notification': OfflineNotification,
        'modal-user-desktop-component': ModalUser,
        'modal-user-mobile-component': ModalUserMobile,
        'modal-register-desktop-component': RegisterModal,
        'modal-recover-password-desktop-component': RecoverPasswordModal,
        'modal-event-desktop-component': ModalEvent,
        'modal-bang-desktop-component': ModalBang,
        'modal-chat-desktop-component': ModalChat,
        'modal-chat-mobile-component': ModalMobileChat,
        'modal-chat-event-desktop-component': ModalChatEvent,
        'modal-chat-group-desktop-component': ModalChatGroup,
        'modal-chat-preview': ModalChatPreview,
        'alert': Alert,
        'modal-dialog': Dialog,
        'health-alert': HealthAlert,
    },
    computed: {
        isLightLoading() {
            return this.loading == 'light'
        },
        requirementsAlert() {
            return this.$store.state.requirementsAlert
        },
        announceAlert() {
            return this.$store.state.announceAlert
        },
        isBuddyAtWork() {
            let atWork = false;

            return atWork
        }
    },
    methods: {
        tapped() {
            this.$store.commit('setTapped')
        },
        requirementsAlertRedirect() {
            this.$store.dispatch('requirementsAlertRedirect')
        },
        requirementsAlertHide() {
            this.$store.dispatch('requirementsAlertHide')
        },
        announceAlertHide() {
            this.$store.dispatch('announceAlertHide')
        },
        pushRouteHistory(url) {
            if (this.routesHistory[this.routesHistory.length - 1] != url) {
                this.routesHistory.push(url);
            }

            if (this.routesHistory.length > 40) {
                this.routesHistory = this.routesHistory.slice(-20);
            }
        },
        setLanguage(lang) {
            this.lang = lang; //for reactivity
            this.$validator.localize(lang); //for validations
            setUserLanguage(lang); //for storage
        },
        log() {
            if (process.env.NODE_ENV === 'development') {
                console.log.apply(console, arguments);
            }
        },
        showLoading(newLoad) {
            this.loading = !!newLoad;
        },
        showLightLoading(newLoad) {
            if (newLoad) {
                this.loading = 'light'
            } else {
                this.loading = false
            }
        },
        hideOnlyLightLoading() {
            if (this.loading == 'light') {
                this.loading = false
            }
        },
        isLoading() {
            return this.loading;
        },

        trans(word, args) {
            return this.$t(word, args);
        },

        addListeners() {
            // When window showing and hiding
            this.addWindowVisibilityListener()

            // Chat listeners, notifications listeners and etc.
            initEchoListeners();
        },
        checkDesktopRedirect(url) {
            if (url === '/register') {
                this.$router.push({name: 'home'});
            }
        },
        addWindowVisibilityListener() {
            let hidden, visibilityChange;

            if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support
                hidden = "hidden";
                visibilityChange = "visibilitychange";
            } else if (typeof document.msHidden !== "undefined") {
                hidden = "msHidden";
                visibilityChange = "msvisibilitychange";
            } else if (typeof document.webkitHidden !== "undefined") {
                hidden = "webkitHidden";
                visibilityChange = "webkitvisibilitychange";
            }

            document.addEventListener(visibilityChange, () => {
                // Windows was hidden
                if (document[hidden]) {
                    console.log('[Poll] Window is hidden. Stop polling.')
                    store.dispatch('disableUserOnlineStatusPolling')

                // Windows was shown
                } else {
                    console.log('[Poll] Window is visible. Start polling.')
                    store.dispatch('enableUserOnlineStatusPolling')

                    // Update worker
                    window.workerReg && window.workerReg.update();
                }
            });
        },
    },
    async created() {
        this.isApplicationLoading = true;
        console.log('[App] Created')
        await this.$store.dispatch('loadCurrentUserInfo').then(async() => {
            //app.$emit('profileLoaded', true)
            console.log('[Poll] App mounted. Profile loaded. Start polling.')
            store.dispatch('enableUserOnlineStatusPolling')

            // Load different filters from local storage
            await this.$store.dispatch(discoverModule.actions.filter.loadFromLocalStorage);
            await this.$store.dispatch(eventsModule.actions.initFilter);

            this.isApplicationLoading = false;
            console.log('[App] Loaded')
        })
    },
    mounted() {
        this.$recaptchaLoaded().then(() => {
            let currentUrl = this.$route.fullPath;
            const recaptcha = this.$recaptchaInstance;

            if (currentUrl === '/register') {
                recaptcha.showBadge()
            } else {
                recaptcha.hideBadge()
            }

        })

        console.log('[App] Mounted')
        // Load profile on page load/reload
        
        // One signal manipulations for app's
        this.verifyOneSignalPlayer();

        // Add global listeners
        this.$nextTick(function(){
            this.addListeners();
            setTimeout(function() {
                window.pageLoaded = true;
            }, 1500);
        });
    },
    beforeMount() {
        console.log('[App] Before mounted');
        let currentUrl = this.$route.fullPath;

        if (app.isDesktop) {
            this.checkDesktopRedirect(currentUrl);
        }
    },
    mediaQueries: window.mediaQueries
});

window.app = app;

app.$mount('#app');
