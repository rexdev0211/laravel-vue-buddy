<template>
	<div>
		<NotificationsTopBar :tab="tab" :set-tab="setTab" :reset="reset" />

		<div class="tab-content-wrapper visits-tabs visitors" v-if="tab === 'visitors'">
            <div class="tab-content-inner">
                <div class="tab-content box">
                    <div class="faces">
                        <div class="face" :dusk="'visitor-' + visitRow.visitor.id"
                        	v-for="(visitRow, index) in visitors"
                            :class="{'online': visitRow.visitor.isOnline, 'was-online': visitRow.visitor.wasRecentlyOnline && !visitRow.visitor.isOnline, 'blurry': isVisitBlurry(index)}">
                            <a @click="openVisitProfile(visitRow.visitor.link || visitRow.visitor.id, index)"></a>
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

                    <infinite-loading ref="infiniteLoadingVisitors" @infinite="getVisitors" spinner="bubbles" :distance="0">
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

        <div class="tab-content-wrapper visits-tabs taps" v-if="tab === 'notifications'">
            <div class="tab-content-inner">
                <div class="tab-content box">
                    <div class="faces">
                        <div class="face" v-for="notification in notifications"
                        	:class="{'online': notification.user_from.isOnline, 'was-online': notification.user_from.wasRecentlyOnline && !notification.user_from.isOnline}">
                        	<a href="javascript:void(0)" @click="openUserModal(notification.user_from.id, 8)"></a>
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

                    <infinite-loading ref="infiniteLoadingNotifications" @infinite="getNotifications" spinner="bubbles" :distance="0">
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

        <div class="tab-content-wrapper visits-tabs visited" v-if="tab === 'visited'">
            <div class="tab-content-inner">
                <div class="tab-content box">
                    <div class="faces">
                        <div class="face" :dusk="'visited-' + visitRow.visited.id"
                        	v-for="(visitRow, index) in visited"
                            :class="{'online': visitRow.visited.isOnline, 'was-online': visitRow.visited.wasRecentlyOnline && !visitRow.visited.isOnline, 'blurry': isVisitBlurry(index)}">
                            <a href="javascript:void(0)" @click="openVisitProfile(visitRow.visited.link || visitRow.visited.id, index)"></a>
                            <div class="img" 
                                :class="{'is-deleted': !!visitRow.visited.deleted_at}"
                                :style="!visitRow.visited.deleted_at && {'background': `url(${visitRow.visited.photo_small}) no-repeat center / cover`}"></div>
                            <div class="details">
                                <div class="name">{{ visitRow.visited.name }}</div>
                                <div class="timeago">{{ timeAgo(visitRow.idate) }}</div>
                            </div>
                        </div>
                    </div>

                    <infinite-loading ref="infiniteLoadingVisited" @infinite="getVisited" spinner="bubbles" :distance="0">
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
</template>

<script>
    import {mapState, mapGetters, mapActions} from 'vuex';
    import notificationsModule from '@notifications/module/store/type';
    import NotificationsTopBar from '@notifications/views/widgets/NotificationsTopBar.vue';

    export default {
      mixins: [
        require('@general/lib/mixin').default,
        require('@general/lib/mixin-tap').default
      ],
      data() {
        return {
          tab: 'notifications'
        }
      },
      components: {
        NotificationsTopBar,
      },
      methods: {
        setTab(tab) {
          this.$parent.$parent.$refs.vueCustomScrollbar.$forceUpdate();
          this.tab = tab;
          this.$store.dispatch(notificationsModule.actions.setVisibleExclusive, { group: tab, visible: true })

          if (
              this.userHasNotifications
              ||
              this.userHasNewNotifications
              ||
              this.userHasNewVisitors
          ) {
            let newValue = { has_notifications: false }
            if (this.tab === 'notifications' && this.userHasNewNotifications) {
              newValue.has_new_notifications = false
            } else if (this.tab === 'visitors' && this.userHasNewVisitors) {
              newValue.has_new_visitors = false
            }
            store.commit('updateUser', newValue)
            axios.post('/api/updateUser', newValue)
          }

          this.$nextTick(function () {
            this.reset()
          })
        },
        reset(){
          console.log('[Notifications] Reset')

          if (this.tab === 'notifications' && this.$refs.infiniteLoadingNotifications) {
            this.$store.dispatch(notificationsModule.actions.notifications.set, [])
            this.$refs.infiniteLoadingNotifications.stateChanger.reset()
          } else if (this.tab === 'visitors' && this.$refs.infiniteLoadingVisitors) {
            this.$store.dispatch(notificationsModule.actions.visitors.set, [])
            this.$refs.infiniteLoadingVisitors.stateChanger.reset()

          } else if (this.tab === 'visited' && this.$refs.infiniteLoadingVisited) {
            this.$store.dispatch(notificationsModule.actions.visited.set, [])
            this.$refs.infiniteLoadingVisited.stateChanger.reset()
          }
        }
      },
      computed: {
        ...mapGetters([
          'userHasNewNotifications',
          'userHasNewVisitors'
        ]),
        ...mapState({
          notifications: state => state.notificationsModule.notifications,
          visitors: state => state.notificationsModule.visitors,
          visited: state => state.notificationsModule.visited
        }),
      },
      watch: {
        notifications() {
          this.$parent.$parent.$refs.vueCustomScrollbar.$forceUpdate();
        },
        visitors() {
          this.$parent.$parent.$refs.vueCustomScrollbar.$forceUpdate();
        },
        visited() {
          this.$parent.$parent.$refs.vueCustomScrollbar.$forceUpdate();
        }
      },
      created(){
        this.reset()
      },
      mounted() {
        this.setTab(this.tab)
      },
      destroyed() {
        this.$store.dispatch(notificationsModule.actions.setVisibility, { group: 'notifications', visible: false })
        this.$store.dispatch(notificationsModule.actions.setVisibility, { group: 'visitors', visible: false })
        this.$store.dispatch(notificationsModule.actions.setVisibility, { group: 'visited', visible: false })
      }
    }
</script>
