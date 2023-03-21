<template>
  <div class="w-root off-canvas-wrapper">
    <div style="overflow: hidden !important;" class="off-canvas-content w-views" data-off-canvas-content>
      <vue2-gesture :type="'swipeLeft'" :call="handleGesture.bind(this, 'swipeLeft')">
        <vue2-gesture :type="'swipeRight'" :call="handleGesture.bind(this, 'swipeRight')">
          <div id="application-wrapper" ref="mobileScrollTopContainer">
            <header>
              <div class="user-box" v-if="profile.id"
                   @click="toggleProfileSidebar(null)"
                   :class="{'online': !discreetModeEnabled}">
                <div class="img" :style="{'background': `url(${defaultPhoto}) no-repeat center / cover`}"></div>
              </div>
              <div class="buttons">
                <div id="visits" class="button visits" @click="goTo('/notifications')"
                    :class="{'notificated': userHasNotifications}">
                    <div class="notificated-icon" v-if="userHasNotifications"></div>
                </div>
              </div>
              <button class="hamburger calendar-menu-button"
                  type="button"
                  v-if="!isSidebarActive"
                  @click="showSidebar"
                  :class="{'notificated': hasMembershipRequests}">
                  <span class="hamburger-box">
                      <span class="hamburger-inner"></span>
                  </span>
              </button>
            </header>

            <div class="mobileSidebarHolder positionLeft"
                 :class="{'active': sidebar.profile.visible}">
              <div class="mobileSidebarHide" @click="toggleProfileSidebar(false)"></div>
              <div class="mobileSidebar">
                <ProfileMenu/>
              </div>
            </div>

            <MobileSidebar>
              <div class="calendar-menu">
                <div class="inner">
                  <div class="add-event-box">
                    <a id="add-event-link" @click="createEvent('event')">{{ trans('events.new_event') }}</a>
                  </div>
                  <div class="box">
                    <div class="headline">{{ trans('events.my_events') }}</div>
                    <div class="events" v-if="myEvents.length">
                      <div class="event" v-for="event in myEvents" :data-event="event.membership"
                           @click="openEvent(event.id, event.type)"
                           :class="{'notificated': membershipRequests(event.id)}">
                        <div class="img"
                             :style="{'background': `url(${event.photo_small}) no-repeat center / cover`}">
                          <div v-if="event.type === 'guide'"
                               :class="{'pending': event.status === 'pending', 'rejected': event.status === 'declined'}"
                          >
                          </div>
                        </div>
                        <div class="details" v-if="event.type === 'bang'">
                          <div class="status" v-if="event.membership === 'host'">{{ trans('host') }}</div>
                          <div class="status" v-else-if="event.membership === 'member'">{{ trans('member') }}</div>
                          <div class="status" v-else-if="event.membership === 'requested'">{{ trans('pending') }}</div>
                          <div class="location notranslate" v-if="event.locality"><span>{{ event.locality }}</span></div>
                          <div class="date" v-if="event.date"><span>{{ event.date | formatDate('day-months-year') }}</span></div>
                        </div>
                        <div class="details" v-else-if="event.type === 'guide'">
                            <div class="event-title">{{ event.title }}</div>
                            <div class="location notranslate" v-if="event.locality"><span>{{ event.locality }}</span></div>
                            <div class="date" v-if="event.date"><span>{{ event.date | formatDate('day-months-year') }}</span></div>
                        </div>
                        <div class="details" v-else>
                          <div class="event-title">{{ event.title }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </MobileSidebar>

            <div class="content-wrapper">
              <div class="calendar-list">
                <div class="center">
                  <div class="tabs calendar-tabs">
                    <div class="tab"
                         v-for="typeItem in types"
                         @click="setType(typeItem)"
                         :class="{'active': type === typeItem}">
                      <span :class="{'notificated-bang':(typeItem === 'bang' && getInvitationsToBang.length && type !== typeItem)}">{{ trans(`events.type.${typeItem}`) }}</span>
                    </div>
                  </div>

                  <div id="js-events-content" class="tab-content-wrapper calendar-tabs" :class="{[type]: true}">
                    <div class="tab-content-inner">
                      <div class="tab-content">
                        <div class="events">

                          <StickyEventsList :list="stickyEvents" :type="type"/>
                          <InnerList/>

                        </div>

                        <div id="add-event" class="add-event-button"
                             @click="createEvent('event')" :title="trans('events.new_event')">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <BottomBar tab="events"></BottomBar>
          </div><!--#application-wrapper-->
        </vue2-gesture>
      </vue2-gesture>
    </div><!--w-views-->
  </div><!--w-root-->
</template>

<style scoped lang="scss">
.notificated-bang {
  &:before {
    content: "";
    position: absolute;
    top: 15px;
    margin-left:-13px;
    z-index: 1;
    width: 10px;
    height: 10px;
    border-radius: 2px;
    background: #FF0000;
  }
}
</style>

<script>
import {mapState, mapGetters, mapActions} from 'vuex';
import InfiniteLoading from 'vue-infinite-loading';

import BottomBar from '@buddy/views/widgets/BottomBar.vue';
import MobileSidebar from '@buddy/views/widgets/Sidebar.vue';
import ProfileMenu from "@profile/views/widgets/ProfileMenu.vue";
import StickyEventsList from '@events/views/widgets/StickyEventsList.vue';
import InnerList from '@events/views/widgets/InnerList.vue';

import eventsModule from '@events/module/store/type'
import {_type as sidebarType} from '@general/modules/sidebar'

export default {
  mixins: [
    require('@general/lib/mixin').default,
    require('@general/lib/mixin-events').default,
    require('@general/lib/mixin-bang').default,
    require('@general/lib/mixin-chat').default,
  ],
  data: () => ({
    types: [
      'guide',
      'fun',
      'bang'
    ],
    scroll: 0,
    refresh: null,
    isDeactivated: false
  }),
  components: {
    BottomBar,
    MobileSidebar,
    ProfileMenu,
    InfiniteLoading,
    StickyEventsList,
    InnerList
  },
  computed: {
    ...mapState({
      myEvents: 'myEvents',
      sidebar: state => state.sidebar,
      profile: 'profile',
      event: 'event',
    }),
    ...mapGetters({
      type: eventsModule.getters.type,
      userProfile: 'userProfile',
      isSidebarActive: sidebarType.getters.active,
      stickyEvents: eventsModule.getters.stickyEvents,
      discreetModeEnabled: 'discreetModeEnabled',
      userHasNotifications: 'userHasNotifications',
      getInvitationsToBang: 'getInvitationsToBang',
    }),
    defaultPhoto() {
      let avatars = this.profile.avatars
      return _.get(avatars, 'merged.photo_small', '/assets/img/default_180x180.jpg')
    },
    hasMembershipRequests() {
      let data = false

      for (let i in this.myEvents) {
        let event = this.myEvents[i]

        if (this.membershipRequests(event.id) || event.is_new) {
          data = true
        }
      }

      return data
    }
  },
  methods: {
    ...mapActions({
      requirementsAlertShow: 'requirementsAlertShow',
      showSidebar: sidebarType.actions.show,
      hideSidebar: sidebarType.actions.hide,
      getStickyEvents: eventsModule.actions.getStickyEvents,
    }),
    toggleProfileSidebar(value) {
      if (value === null) {
        value = !this.sidebar.profile.visible
        if (value) {
          this.hideSidebar
        }
      }
      this.$store.dispatch('updateSidebarVisibility', {index: 'profile', value})
    },
    handleGesture(str, e) {
      this.scroll = 0;

      if (str === 'swipeRight') {
        // hide sidebar
        if (this.isSidebarActive) {
          this.hideSidebar()

          // fun < bang
        } else if (this.type === 'bang') {
          this.setType('fun')

          // friends < fun
        } else if (this.type === 'fun') {
          this.setType('friends')

          // show profile sidebar
        } else if (this.sidebar.profile.visible) {
          this.toggleFilterSidebar(true)
        }
      }

      if (str === 'swipeLeft') {
        // hide profile sidebar
        if (this.sidebar.profile.visible) {
          this.toggleFilterSidebar(false)

          // friends > fun
        } else if (this.type === 'friends') {
          this.setType('fun')

          // fun > bang
        } else if (this.type === 'fun') {
          this.setType('bang')

          // bang > sidebar
        } else if (this.type === 'bang') {
          this.showSidebar()
        }
      }
    },
    setType(type) {
      console.log('setType', {type})
      if (app.isApp && this.userProfile.view_sensitive_events === 'no' && type === 'fun') {
        this.requirementsAlertShow('change_settings')
      } else {
        this.$store.dispatch(eventsModule.actions.setType, type)
        app.$emit('reload-events')
      }
      // this.$store.dispatch('showHealthAlert')
    },
    pullRefresh() {
      app.$emit('reload-events')
    },
    checkScroll(event) {
      if (event) {
        let currentScroll = event.target.scrollTop;

        this.scroll = currentScroll
        if (currentScroll > 5) {
            if (this.refresh) {
              this.refresh.destroy()
            }
        } else {
          this.refresh = this.attachPullToRefresh('#js-events-content')
        }
        if (!this.isDeactivated) {
          this.setEventsAroundPosition();
        }
      }
    },
    setScrollEvent() {
      if (app.isMobile && this.$refs && this.$refs.mobileScrollTopContainer) {
        let container = document.getElementById('application-wrapper');
        container.addEventListener('scroll', this.checkScroll, true)
      }
    },
    setEventsAroundPosition() {
        app.prevEventsAroundPosition = this.$refs.mobileScrollTopContainer ? this.$refs.mobileScrollTopContainer.scrollTop : 0
    }
  },
  watch: {
    'userProfile.view_sensitive_events': function (value) {
      console.log('[Events List Mobile] userProfile watcher', {value})
      if (app.isApp && value === 'no') {
        this.setType('friends')
      }
    }
  },
  mounted() {
    console.log('[Events] Mounted')
    this.loadScrollTopButton("js-events-content");
    this.refresh = this.attachPullToRefresh('#js-events-content')

    this.setScrollEvent()

    if (this.$route.query.eventId) {
      this.openEvent(this.$route.query.eventId, 'event');
    }

    if (this.$route.query.bangId) {
      this.openEvent(this.$route.query.bangId, 'bang');
    }

    this.getStickyEvents()
  },
  activated() {
      if (this.isMobile) {
        app.$emit('reload-events')
      }

      this.isDeactivated = false
      this.setScrollEvent()
  },
  deactivated() {
      this.isDeactivated = true;
  }
}
</script>
