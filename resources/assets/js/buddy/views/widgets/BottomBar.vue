<template>
    <footer>
        <div dusk="section-discover" :class="{'active': tab === 'discover'}" class="button discover">
            <a @click="goToTab('discover')"></a>
        </div>
        <div dusk="section-chat" :class="{'active': tab === 'chat', 'notificated': userHasNewMessages}" class="button chat">
            <a @click="goToTab('chat')"></a>
        </div>
        <div dusk="section-events" :class="{'active': tab === 'events', 'notificated': userHasEventNotifications || getInvitationsToBang.length}" class="button calendar">
            <a @click="goToTab('events')"></a>
        </div>
        <div dusk="section-clubs" :class="{'active': tab === 'clubs', 'notificated': userHasClubNotifications || getInvitationsToClub.length}" class="button group">
            <a @click="goToTab('clubs')"></a>
        </div>
    </footer>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';
    import eventsModule from "@events/module/store/type";

    export default {
        data() {
            return {
                widgetOpen:    false,
                widgetTimeout: null,
            }
        },
        mixins: [require('@general/lib/mixin').default],
        props: ['tab'],
        methods: {
            ...mapActions([
                'requirementsAlertShow',
                'goToRush',
            ]),
            goToTab(tab) {
                if(tab === 'chat') {
                    this.goTo(app.lastChatPage);
                } else if(tab === 'events') {
                    this.goTo('/events');
                } else if(tab === 'profile') {
                    this.goTo('/profile/pro');
                } else if(tab === 'notifications') {
                    this.goTo('/notifications');
                } else if(tab === 'clubs') {
                    this.goTo('/clubs');
                } else {
                    this.goTo('/discover');
                }

				      if (this.tab == tab) {
                    if (app.isMobile && this.$parent.$refs && this.$parent.$refs.mobileScrollTopContainer) {
                        $(this.$parent.$refs.mobileScrollTopContainer).animate({scrollTop: 0}, 500)
                    }
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
            }
        },
        computed: {
            ...mapGetters([
                'discreetModeEnabled',
                'userHasNewMessages',
                'userHasEventNotifications',
                'getInvitationsToBang',
                'userHasClubNotifications',
                'getInvitationsToClub',
            ])
        },
    }
</script>
