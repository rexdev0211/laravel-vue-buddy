<template>
    <div class="w-root">
        <div class="w-views">
            <vue2-gesture :type="'swipeLeft'" :call="handleGesture.bind(this, 'swipeLeft')">
                <vue2-gesture :type="'swipeRight'" :call="handleGesture.bind(this, 'swipeRight')">

                    <div class="secondary-menu visits-menu">
                        <div class="secondary-menu-header">
                            <i class="back" @click="goTo('/discover')"></i>
                        </div>

                        <div class="secondary-menu-body">
                            <NotificationsTopBar tab="notifications" :set-tab="() => {}" :reset="() => {}" />

                            <div class="wrap" id="js-notifications-content">
                                <div class="tab-content-wrapper visits-tabs taps">
                                    <div class="tab-content-inner">
                                        <div class="tab-content box">
                                            <div class="faces">
                                                <div v-for="notification in notifications"
                                                     class="face" 
                                                    @click="goToProfile(notification.user_from, 2)"
                                                    :class="{
                                                        'online': notification.user_from.isOnline,
                                                        'was-online': notification.user_from.wasRecentlyOnline && !notification.user_from.isOnline
                                                }">
                                                    <div class="img" 
                                                        :class="{'is-deleted': !!notification.user_from.deleted_at}"
                                                        :style="!notification.user_from.deleted_at && {'background': `url(${notification.user_from.photo_small}) no-repeat center / cover`}">
                                                    </div>
                                                    <div class="details">
                                                        <div class="detail-box">
                                                        <div class="name">{{ notification.user_from.name }}</div>
                                                        <div class="event-title"
                                                            v-if="notification.type == 'event'">
                                                            {{ notification.notification_event.title }}
                                                        </div>
                                                        </div>
                                                        <div v-if="notification.type == 'wave'" class="taps"
                                                            :class="notification.sub_type">
                                                        </div>
                                                        <div v-if="notification.type == 'event'" class="taps love-green"></div>
                                                        <div class="timeago">{{ timeAgo(notification.idate) }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <infinite-loading ref="infiniteLoadingNotifications"
                                                        @infinite="getNotifications"
                                       force-use-infinite-wrapper="#js-notifications-content"
                                                          spinner="bubbles"
                                            >
                                                <span slot="no-results">
                                                    {{ trans('no_taps') }}
                                                </span>
                                                <span slot="no-more">
                                                    {{ trans('no_more_taps') }}
                                                </span>
                                            </infinite-loading>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </vue2-gesture>
            </vue2-gesture>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import {mapState, mapGetters} from 'vuex';
    import NotificationsTopBar from '@notifications/views/widgets/NotificationsTopBar.vue'
    import notificationsModule from '@notifications/module/store/type';

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-tap').default
        ],
        components: {
            NotificationsTopBar
        },
        methods: {
            ...mapGetters([
                'userHasNotifications',
                'userHasNewNotifications',
            ]),
            goToProfile(user) {
                if (user.deleted_at) {
                    this.showErrorNotification('profile_is_deleted')
                } else {
                    this.goTo('/user/' + user.id)
                }
            },
            handleGesture(str, e) {
                if (str == 'swipeRight') {
                    this.goTo('/visitors')
                }
                if (str == 'swipeLeft') {
                    this.goTo('/visited')
                }
            },
            activate(){
                this.$store.dispatch(notificationsModule.actions.setVisibleExclusive, { group: 'notifications', visible: true })

                if (this.userHasNotifications || this.userHasNewNotifications) {
                    let newValue = { has_notifications: false }
                    if (this.userHasNewNotifications) {
                        newValue.has_new_notifications = false
                    }
                    store.commit('updateUser', newValue)
                    axios.post('/api/updateUser', newValue)
                }
            },
            reload(){
                console.log('[Notifications] Reload')
                this.$store.dispatch(notificationsModule.actions.notifications.set, [])
                if (this.$refs.infiniteLoadingNotifications){
                    console.log('[Notifications] $InfiniteLoading:reset')
                    this.$refs.infiniteLoadingNotifications.$emit('$InfiniteLoading:reset');
                } else {
                    console.log('[Notifications] No InfiniteLoading found')
                }
            }
        },
        computed: {
            ...mapState({
                notifications: state => state.notificationsModule.notifications
            }),
        },
        activated(){
            console.log('[Notifications] Activated')
			this.activate()
            this.softReload('Notifications')
        },
        mounted() {
            console.log('[Notifications] Mounted')
            app.$on('invalidate-notifications', this.invalidate)
            this.initPullToRefresh('#js-notifications-content')
        },
        destroyed() {
            console.log('[Notifications] Destroyed')
            this.$store.dispatch(notificationsModule.actions.setVisibility, { group: 'notifications', visible: false })
            app.$off('invalidate-notifications')
        },
        deactivated() {
            console.log('[Notifications] Deactivated')
            this.$store.dispatch(notificationsModule.actions.setVisibility, { group: 'notifications', visible: false })
        }
    }
</script>
