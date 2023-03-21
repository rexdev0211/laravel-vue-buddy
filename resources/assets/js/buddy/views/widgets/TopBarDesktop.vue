<template>
    <span style="display: contents;">
        <header>
            <div class="part">
                <div class="user-box" v-if="profile.id"
                    @click="toggleProfileSidebar(null)"
                    :class="{'online': !discreetModeEnabled}">
                    <div class="img" :style="{'background': `url(${defaultPhoto}) no-repeat center / cover`}"></div>
                </div>
                <div class="buttons">
                    <div class="button mask"
                        v-if="userIsPro"
                        :class="{'active': discreetModeEnabled}"
                        @click="trySwitchDiscreetMode">
                    </div>
                    <div dusk="discover-filters" id="filter"
                        v-if="initialSection === 'discover'"
                        :class="{'activated': enabledFiltersCount}"
                        @click="toggleFilterSidebar(null)"
                        class="button filter">
                        <span class="quantity" v-if="enabledFiltersCount">{{ enabledFiltersCount }}</span>
                    </div>
                    <div id="visits" class="button visits"
                        :class="{'notificated': userHasNotifications}"
                        @click="toggleNotifications">
                        <div class="notificated-icon" v-if="userHasNotifications"></div>
                    </div>
                </div>
            </div>

            <div class="logo-header" v-if="isDesktop">
                <router-link to="/discover">
                    <img class="full" src="/main/img/buddy-logo.svg" alt="Buddy | Buddies & Benefits">
                    <img class="short" src="/main/img/buddy-logo-short.svg" alt="Buddy | Buddies & Benefits">
                </router-link>
            </div>
            <div class="part navigation" v-if="isDesktop">
                <div dusk="section-discover" :class="{'active': initialSection === 'discover'}" class="button discover">
                    <router-link dusk="section-discover" to="/discover"></router-link>
                </div>
                <div dusk="section-chat" class="button chat" @click="toggleChatPreview" :class="{'active': initialSection === 'chat' || section === 'chat-preview', 'notificated': userHasNewMessages}">
                </div>
                <div :class="{'active': initialSection === 'events', 'notificated': userHasEventNotifications || getInvitationsToBang.length}" class="button calendar">
                    <router-link dusk="section-events" to="/events"></router-link>
                </div>
                <div :class="{'active': initialSection === 'clubs', 'notificated': userHasClubNotifications || getInvitationsToClub.length}" class="button group">
                    <router-link dusk="section-clubs" to="/clubs"></router-link>
                </div>
                <!-- <div id="pro-menu" class="button group" :class="{'active': initialSection === 'profile'}">
                    <router-link dusk="section-clubs" to="/clubs"></router-link>
                </div> -->
            </div>
        </header>

        <div class="mobileSidebarHolder positionLeft notifications forDesktop"
             :class="{'active': notificationsVisible}">
            <div class="mobileSidebarHide" @click="closeNotifications"></div>
            <div class="mobileSidebar">
                <transition :name="'slide-in-left'" mode="out-in" type="animation">
                    <div class="secondary-menu visits-menu" style="padding-bottom: 170px" dusk="notifications-dropdown"
                        v-if="notificationsVisible" id="notif-dropdown"
                        data-close-on-click="false">
                        <div class="secondary-menu-header">
                            <i class="back" @click="closeNotifications"></i>
                        </div>

                        <vue-custom-scrollbar ref="vueCustomScrollbar" class="secondary-menu-body">
                            <Notifications/>
                        </vue-custom-scrollbar>
                    </div>
                </transition>
            </div>
        </div>

        <div class="mobileSidebarHolder positionLeft forDesktop"
             :class="{'active': sidebar.profile.visible, 'secondary-menu-opened': profileEditOpened || profileLocationOpened || profilePhotosOpened || profileVideosOpened || profileSettingsOpened || profileShareOpened || profileHelpOpened}">
            <div class="mobileSidebarHide" @click="toggleProfileSidebar(false)"></div>
            <div class="mobileSidebar">
                <ProfileMenu/>

                <transition name="slide-in-left" mode="out-in" type="animation">
                    <ProfileEdit v-if="profileEditOpened"></ProfileEdit>
                    <ProfileLocation v-if="profileLocationOpened"></ProfileLocation>
                    <ProfilePhotos v-if="profilePhotosOpened"></ProfilePhotos>
                    <ProfileVideos v-if="profileVideosOpened"></ProfileVideos>
                    <ProfileSettings v-if="profileSettingsOpened"></ProfileSettings>
                    <ProfileHelp v-if="profileHelpOpened"></ProfileHelp>
                    <ProfileShare v-if="profileShareOpened"></ProfileShare>
                </transition>
            </div>
        </div>

        <div class="mobileSidebarHolder positionLeft forDesktop" v-if="section === 'discover' || section === 'chat-preview'"
             :class="{'active': sidebar.filter.visible}">
            <div class="mobileSidebarHide" @click="toggleFilterSidebar(false)"></div>
            <div class="mobileSidebar filterSidebar">
                <FiltersForms/>
            </div>
        </div>
    </span>
</template>

<script>
    import {mapState, mapActions, mapGetters} from 'vuex';

    import ProfileMenu from "@profile/views/widgets/ProfileMenu.vue";
    import ProfileEdit from '@profile/views/desktop/page/ProfileEdit.vue';
    import ProfileLocation from '@profile/views/desktop/page/ProfileLocation.vue';
    import ProfilePhotos from '@profile/views/desktop/page/ProfilePhotos.vue';
    import ProfileVideos from '@profile/views/desktop/page/ProfileVideos.vue';
    import ProfileSettings from '@profile/views/desktop/page/ProfileSettings.vue';
    import ProfileHelp from '@profile/views/desktop/page/ProfileHelp.vue';
    import ProfileShare from '@profile/views/desktop/page/ProfileShare.vue';

    import FiltersForms from '@discover/views/widgets/FiltersForms.vue';

    import Conversations from '@chat/views/widgets/Conversations'
	  import Notifications from '@notifications/views/widgets/Notifications'

    import discoverModule from '@discover/module/store/type';
    import notificationsModule from '@notifications/module/store/type';
    import eventsModule from "@events/module/store/type";

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    import Vue from 'vue'
    import vClickOutside from 'v-click-outside'
    Vue.use(vClickOutside)

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-discover').default,
            require('@general/lib/mixin-chat').default,
        ],
        data() {
            return {
                widgetOpen: false,
                widgetTimeout: null,
                section: null,
                conversationsVisible: false,
                notificationsVisible: false,
            }
        },
		components:{
            ProfileMenu,
            ProfileEdit,
            ProfileLocation,
            ProfilePhotos,
            ProfileVideos,
            ProfileSettings,
            ProfileHelp,
            FiltersForms,
            ProfileShare,
            Conversations,
            Notifications,
            vueCustomScrollbar,
		},
        methods: {
            ...mapActions([
                'goToRush',
                'signalMessagesSeen',
                'trySwitchDiscreetMode',
            ]),
            showOverlay(){
                $('.w-app').addClass('overlay');
            },
            hideOverlay(){
                $('.w-app').removeClass('overlay');
            },
            toggleChatPreview() {
                if (this.previewMode === 'chat-preview'){
                    this.closeChatPreview()
                } else {
                    this.showChatPreview()
                }
            },
            toggleProfileSidebar(value) {
                if (value === null){
                    value = !this.sidebar.profile.visible
                }
                this.$store.dispatch('updateSidebarVisibility', {index: 'profile', value})
                this.closeNotifications()
            },
            toggleFilterSidebar(value) {
                if (value === null){
                    value = !this.sidebar.filter.visible
                    if (value) {
                        this.toggleProfileSidebar(false)
                        this.closeNotifications()
                    }
                }

                this.$store.dispatch('updateSidebarVisibility', {index: 'filter', value})
                if (!value) {
                  this.resetUsersAround()
                }
            },
            resetSearchUsers() {
                this.searchInput = ''
                this.$store.dispatch(discoverModule.actions.filter.set, {
                    key: 'filterName',
                    value: '',
                    queueRefresh: true
                })
            },
            setfFlterType(value) {
                this.$store.dispatch(discoverModule.actions.filter.set, {
                    key: 'filterType',
                    value,
                    refresh: true
                })
            },
            closeNotifications(){
                if (this.notificationsVisible) {
                    this.notificationsVisible = false
                    if (!this.conversationsVisible) {
                        this.section = this.initialSection
                        this.hideOverlay()
                    }
                }
            },
            closeConversations(){
                if (this.conversationsVisible) {
                    this.conversationsVisible = false
                    if (!this.notificationsVisible) {
                        this.section = this.initialSection
                        this.hideOverlay()
                    }
                }
            },
            closeConversationsMiddleware(event){
                let chatOpened = $('.w-chat__widget').length
                let clickedInsideChat = chatOpened ? $.contains($('.w-chat__widget')[0], event.target) : false
                let chatClicked = chatOpened && clickedInsideChat
                console.log('[TopBarDesktop] Outer click', { chatOpened, clickedInsideChat, chatClicked, event})
                return !chatClicked
            },
            toggleConversations() {
                this.conversationsVisible = !this.conversationsVisible
                if (this.conversationsVisible) {
                    this.section = 'conversations'
                    if (this.userHasNewMessages) {
                        this.signalMessagesSeen()
                    }
                    this.showOverlay()
                } else {
                    this.section = this.initialSection
                    this.hideOverlay()
                }
            },
            toggleNotifications() {
                this.notificationsVisible = !this.notificationsVisible
                if (this.notificationsVisible) {
                    this.showOverlay()
                } else {
                    this.section = this.initialSection
                    this.$store.dispatch(notificationsModule.actions.setVisibility, { group: 'notifications', visible: false })
                    this.$store.dispatch(notificationsModule.actions.setVisibility, { group: 'visitors', visible: false })
                    this.$store.dispatch(notificationsModule.actions.setVisibility, { group: 'visited', visible: false })
                    this.hideOverlay()
                }
            },
            redirectToRush() {
                this.goToRush(this.isApp)
            },
            widgetToggle() {
                let latestWidget = this.$store.state.latestWidget
                if (this.isApp && (!this.$store.state.userIsPro || this.$store.state.profile.view_sensitive_media == 'no')) {
                    latestWidget = null
                }
                switch (latestWidget) {
                    case 'rush':
                        this.goToRush(this.isApp)
                        break;
                }

                this.widgetOpen = !this.widgetOpen
                clearTimeout(this.widgetTimeout)

                let self = this
                if (this.widgetOpen) {
                    this.widgetTimeout = setTimeout(() => {
                        self.widgetOpen = false
                    }, 3000)
                }
            },
        },
        computed: {
            ...mapState({
                sidebar: state => state.sidebar,
                profile: 'profile',
                previewMode: state => state.chatModule.modal.previewMode
            }),
            ...mapGetters([
                'discreetModeEnabled',
                'userHasNewMessages',
                'userHasNotifications',
                'userHasEventNotifications',
                'userHasClubNotifications',
                'profileEditOpened',
                'profileLocationOpened',
                'profilePhotosOpened',
                'profileVideosOpened',
                'profileSettingsOpened',
                'profileHelpOpened',
                'profileShareOpened',
                'getInvitationsToBang',
                'getInvitationsToClub',
            ]),
            defaultPhoto() {
                let avatars = this.profile.avatars

                if (!avatars.adult && (avatars.default?.rejected || avatars.default?.pending)) {
                  return '/assets/img/default_180x180.jpg';
                } else {
                  return _.get(avatars, 'merged.photo_small', '/assets/img/default_180x180.jpg')
                }
            },
            vcoConfig(){
                return {
                    handler: this.closeConversations,
                    middleware: this.closeConversationsMiddleware,
                    events: ['click'],
                    isActive: this.conversationsVisible,
                    detectIFrame: true,
                    capture: false
                }
            },
            initialSection(){
                if (this.$route.name === 'discover') {
                    return 'discover'
                } else if (this.$route.name === 'events') {
                    return 'events'
                } else if (this.$route.name === 'chat') {
                    return 'chat'
                } else if (this.$route.name === 'chat-unread') {
                    return 'chat'
                } else if (this.$route.name === 'chat-favorites') {
                    return 'chat'
                } else if (this.previewMode === 'chat-preview') {
                  return 'chat-preview'
                } else if (this.$route.name === 'profileSubscription') {
                    return 'profile'
                } else {
                    return
                }
            }
        },
        watch: {
          previewMode: function (newVal) {
              if (newVal === 'chat-preview') {
                this.section = 'chat-preview';
              } else {
                this.section = this.initialSection;
              }
          },
          '$route': function () {
            this.section = this.initialSection;
          }
        },
        mounted() {
            this.section = this.initialSection
        },
    }
</script>
<style lang="scss">
  .ps__rail-y {
    right: 0 !important;
    left: unset !important;
  }
</style>