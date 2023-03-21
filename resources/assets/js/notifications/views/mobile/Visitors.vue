<template>
    <div class="w-root">
        <div class="w-views">
            <vue2-gesture :type="'swipeLeft'" :call="handleGesture.bind(this, 'swipeLeft')">

                <div class="secondary-menu visits-menu">
                    <div class="secondary-menu-header">
                        <i class="back" @click="goTo('/discover')"></i>
                    </div>

                    <div class="secondary-menu-body">
                        <NotificationsTopBar tab="visitors" :set-tab="() => {}" :reset="() => {}" />

                        <div class="wrap" id="js-visitors-content">
                            <div class="tab-content-wrapper visits-tabs visitors">
                                <div class="tab-content-inner">
                                    <div class="tab-content box">
                                        <div class="faces">
                                            <div class="face" v-for="(visitRow, index) in visitors"
                                                :class="{'online': visitRow.visitor.isOnline, 'was-online': visitRow.visitor.wasRecentlyOnline && !visitRow.visitor.isOnline, 'blurry': isVisitBlurry(index)}">
                                                <a @click="goToProfile(visitRow.visitor, 2)" :class="{'blurry': isVisitBlurry(index)}"></a>
                                                <div class="img" 
                                                    :class="{'is-deleted': !!visitRow.visitor.deleted_at}"
                                                    :style="!visitRow.visitor.deleted_at && {'background': `url(${visitRow.visitor.photo_small}) no-repeat center / cover`}">
                                                </div>
                                                <div class="details">
                                                    <div class="name">{{ visitRow.visitor.name }}</div>
                                                    <div class="timeago">{{ timeAgo(visitRow.idate) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <infinite-loading ref="infiniteLoadingVisitors"
                                                @infinite="getVisitors"
                               force-use-infinite-wrapper="#js-visitors-content"
                                                  spinner="bubbles"
                                    >
                                        <span slot="no-results">
                                            {{ trans('no_visitors') }}
                                        </span>
                                        <span slot="no-more">
                                            {{ trans('no_more_visitors') }}
                                        </span>
                                    </infinite-loading>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </vue2-gesture>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import {mapState, mapGetters} from 'vuex';
    import InfiniteLoading from 'vue-infinite-loading';
    import NotificationsTopBar from '@notifications/views/widgets/NotificationsTopBar.vue'
    import notificationsModule from '@notifications/module/store/type';

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-tap').default
        ],
        computed: {
            ...mapGetters([
                'userHasNotifications',
                'userHasNewVisitors',
            ]),
            ...mapState({
                visitors: state => state.notificationsModule.visitors
            }),
        },
        components: {
            InfiniteLoading,
            NotificationsTopBar
        },
        methods: {
            goToProfile(user) {
                if (user.deleted_at) {
                    this.showErrorNotification('profile_is_deleted')
                } else {
                    this.goTo('/user/' + user.id)
                }
            },
            handleGesture(str, e) {
                if (str == 'swipeLeft') {
                    this.goTo('/notifications')
                }
            },
            activate(){
                this.$store.dispatch(notificationsModule.actions.setVisibleExclusive, { group: 'visitors', visible: true })

                if (this.userHasNotifications || this.userHasNewVisitors) {
                    let newValue = { has_notifications: false }
                    if (this.userHasNewVisitors) {
                        newValue.has_new_visitors = false
                    }

                    store.commit('updateUser', newValue)
                    axios.post('/api/updateUser', newValue)
                }
            },
            reload(){
                console.log('[Visitors] Reload')
                this.$store.dispatch(notificationsModule.actions.visitors.set, [])
                if (this.$refs.infiniteLoadingVisitors){
                    this.$refs.infiniteLoadingVisitors.$emit('$InfiniteLoading:reset');
                } else {
                    console.log('[Visitors] No InfiniteLoading found')
                }
            },
        },
        activated(){
            console.log('[Visitors] Activated')
            this.activate()
            this.softReload('Visitors')
        },
        mounted() {
            console.log('[Visitors] Mounted')
            app.$on('invalidate-visitors', this.invalidate)
            this.initPullToRefresh('#js-visitors-content')
        },
        destroyed() {
            console.log('[Visitors] Destroyed')
            this.$store.dispatch(notificationsModule.actions.setVisibility, { group: 'visitors', visible: false })
            app.$off('invalidate-visitors')
        },
        deactivated() {
            console.log('[Visitors] Deactivated')
            this.$store.dispatch(notificationsModule.actions.setVisibility, { group: 'visitors', visible: false })
        }
    }
</script>
