<template>
    <div class="w-root">
        <div class="w-views">
            <vue2-gesture :type="'swipeRight'" :call="handleGesture.bind(this, 'swipeRight')">

                <div class="secondary-menu visits-menu">
                    <div class="secondary-menu-header">
                        <i class="back" @click="goTo('/discover')"></i>
                    </div>

                    <div class="secondary-menu-body">
                        <NotificationsTopBar tab="visited" :set-tab="() => {}" :reset="() => {}" />

                        <div class="wrap" id="js-visited-content">
                            <div class="tab-content-wrapper visits-tabs visited">
                                <div class="tab-content-inner">
                                    <div class="tab-content box">
                                        <div class="faces">
                                            <div v-for="(visitRow, index) in visited"
                                                 class="face" 
                                                @click="goToProfile(visitRow.visited, 2)"
                                                :class="{
                                                    'online': visitRow.visited.isOnline,
                                                    'was-online': visitRow.visited.wasRecentlyOnline && !visitRow.visited.isOnline,
                                                    'blurry': isVisitBlurry(index)
                                            }">
                                                <div class="img" 
                                                    :class="{'is-deleted': !!visitRow.visited.deleted_at}"
                                                    :style="!visitRow.visited.deleted_at && {'background': `url(${visitRow.visited.photo_small}) no-repeat center / cover`}">
                                                </div>
                                                <div class="details">
                                                    <div class="name">{{ visitRow.visited.name }}</div>
                                                    <div class="timeago">{{ timeAgo(visitRow.idate) }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <infinite-loading ref="infiniteLoadingVisited"
                                                    @infinite="getVisited"
                                   force-use-infinite-wrapper="#js-visited-content"
                                                      spinner="bubbles"
                                        >
                                            <span slot="no-results">
                                                {{ trans('no_visited') }}
                                            </span>
                                            <span slot="no-more">
                                                {{ trans('no_more_visited') }}
                                            </span>
                                        </infinite-loading>
                                    </div>
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
            ]),
            ...mapState({
                visited: state => state.notificationsModule.visited
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
                if (str == 'swipeRight') {
                    this.goTo('/notifications')
                }
            },
            activate(){
                this.$store.dispatch(notificationsModule.actions.setVisibleExclusive, { group: 'visited', visible: true })

                if (this.userHasNotifications) {
                    let newValue = { has_notifications: false }
                    store.commit('updateUser', newValue)
                    axios.post('/api/updateUser', newValue)
                }
            },
            reload(){
                console.log('[Visited] Reload')
                this.$store.dispatch(notificationsModule.actions.visited.set, [])
                if (this.$refs.infiniteLoadingVisited){
                    this.$refs.infiniteLoadingVisited.stateChanger.reset()
                } else {
                    console.log('[Visited] No InfiniteLoading found')
                }
            }
        },
        activated() {
            console.log('[Visited] Activated')
            this.activate()
            this.softReload('Visited')
        },
        mounted() {
            console.log('[Visited] Mounted')
            app.$on('invalidate-visited', this.invalidate)
            this.initPullToRefresh('#js-visited-content')
        },
        destroyed() {
            console.log('[Visited] Destroyed')
            this.$store.dispatch(notificationsModule.actions.setVisibility, { group: 'visited', visible: false })
            app.$off('invalidate-visited')
        },
        deactivated() {
            console.log('[Visited] Deactivated')
            this.$store.dispatch(notificationsModule.actions.setVisibility, { group: 'visited', visible: false })
        }
    }
</script>
