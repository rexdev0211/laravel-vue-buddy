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
              </header>
  
              <div class="mobileSidebarHolder positionLeft"
                   :class="{'active': sidebar.profile.visible}">
                <div class="mobileSidebarHide" @click="toggleProfileSidebar(false)"></div>
                <div class="mobileSidebar">
                  <ProfileMenu/>
                </div>
              </div>
  
              <!-- <MobileSidebar>
                <div class="calendar-menu">
                  <div class="inner">
                    <div class="add-club-box">
                      <a id="add-club-link" @click="createClub('club')">{{ trans('clubs.new_club') }}</a>
                    </div>
                    <div class="box">
                      <div class="headline">{{ trans('clubs.my_clubs') }}</div>
                      <div class="clubs" v-if="myClubs.length">
                        <div class="club" v-for="club in myClubs" :data-club="club.membership"
                             @click="openClub(club.id, club.type)"
                             :class="{'notificated': membershipRequests(club.id)}">
                          <div class="img"
                               :style="{'background': `url(${club.photo_small}) no-repeat center / cover`}">
                            <div v-if="club.type === 'guide'"
                                 :class="{'pending': club.status === 'pending', 'rejected': club.status === 'declined'}"
                            >
                            </div>
                          </div>
                          <div class="details" v-if="club.type === 'bang'">
                            <div class="status" v-if="club.membership === 'host'">{{ trans('host') }}</div>
                            <div class="status" v-else-if="club.membership === 'member'">{{ trans('member') }}</div>
                            <div class="status" v-else-if="club.membership === 'requested'">{{ trans('pending') }}</div>
                            <div class="location notranslate" v-if="club.locality"><span>{{ club.locality }}</span></div>
                            <div class="date" v-if="club.date"><span>{{ club.date | formatDate('day-months-year') }}</span></div>
                          </div>
                          <div class="details" v-else-if="club.type === 'guide'">
                              <div class="club-title">{{ club.title }}</div>
                              <div class="location notranslate" v-if="club.locality"><span>{{ club.locality }}</span></div>
                              <div class="date" v-if="club.date"><span>{{ club.date | formatDate('day-months-year') }}</span></div>
                          </div>
                          <div class="details" v-else>
                            <div class="club-title">{{ club.title }}</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </MobileSidebar> -->
  
              <div class="content-wrapper">
                <div class="clubs-list">
                  <div class="center">
                    <div class="tabs clubs-tabs">
                      <div class="clubs-tab"
                           v-for="typeItem in types"
                           @click="setType(typeItem)"
                           :class="{'active': type === typeItem}">
                        <span :class="{'notificated-bang':(typeItem === 'my_clubs' && getInvitationsToClub.length && type !== typeItem)}">{{ trans(`clubs.type.${typeItem}`) }}</span>
                      </div>
                    </div>
  
                    <div id="js-clubs-content" class="tab-content-wrapper calendar-tabs" :class="{[type]: true}">
                      <div class="tab-content-inner">
                        <div class="tab-content">
                          <div class="events">
  
                            <!-- <StickyClubsList :list="stickyClubs" :type="type"/> -->
                            <InnerList/>
  
                          </div>
  
                          <div id="add-club" class="add-club-button"
                               @click="createClub()" :title="trans('clubs.new_club')">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
  
              <BottomBar tab="clubs"></BottomBar>
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
  // import StickyClubsList from '@clubs/views/widgets/StickyClubsList.vue';
  import InnerList from '@clubs/views/widgets/InnerList.vue';
  
  import clubsModule from '@clubs/module/store/type'
  import {_type as sidebarType} from '@general/modules/sidebar'
  
  export default {
    mixins: [
      require('@general/lib/mixin').default,
      require('@general/lib/mixin-clubs').default,
      require('@general/lib/mixin-clubs').default,
      require('@general/lib/mixin-bang').default,
      require('@general/lib/mixin-chat').default,
    ],
    data: () => ({
      types: [
        'discover',
        'my_clubs'
        // 'guide',
        // 'fun',
        // 'bang'
      ],
      type: 'discover',
      scroll: 0,
      refresh: null,
      isDeactivated: false
    }),
    components: {
      BottomBar,
      MobileSidebar,
      ProfileMenu,
      InfiniteLoading,
      // StickyClubsList,
      InnerList
    },
    computed: {
      ...mapState({
        // myClubs: 'myClubs',
        sidebar: state => state.sidebar,
        profile: 'profile',
        club: 'club',
      }),
      ...mapGetters({
        // type: clubsModule.getters.type,
        userProfile: 'userProfile',
        isSidebarActive: sidebarType.getters.active,
        // stickyClubs: clubsModule.getters.stickyClubs,
        discreetModeEnabled: 'discreetModeEnabled',
        userHasNotifications: 'userHasNotifications',
        getInvitationsToClub: 'getInvitationsToClub',
      }),
      defaultPhoto() {
        let avatars = this.profile.avatars
        return _.get(avatars, 'merged.photo_small', '/assets/img/default_180x180.jpg')
      },
      hasMembershipRequests() {
        let data = false
  
        // for (let i in this.myClubs) {
        //   let club = this.myClubs[i]
  
        //   if (this.membershipRequests(club.id) || club.is_new) {
        //     data = true
        //   }
        // }
  
        return data
      }
    },
    methods: {
      ...mapActions({
        requirementsAlertShow: 'requirementsAlertShow',
        showSidebar: sidebarType.actions.show,
        hideSidebar: sidebarType.actions.hide,
        // getStickyClubs: clubsModule.actions.getStickyClubs,
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
        this.type = type
        // if (app.isApp && this.userProfile.view_sensitive_clubs === 'no' && type === 'fun') {
        //   this.requirementsAlertShow('change_settings')
        // } else {
        this.$store.dispatch(clubsModule.actions.setType, type)
        app.$emit('reload-clubs')
        // }
        // this.$store.dispatch('showHealthAlert')
      },
      pullRefresh() {
        app.$emit('reload-clubs')
      },
      checkScroll(club) {
        if (club) {
          let currentScroll = club.target.scrollTop;
  
          this.scroll = currentScroll
          if (currentScroll > 5) {
              if (this.refresh) {
                this.refresh.destroy()
              }
          } else {
            this.refresh = this.attachPullToRefresh('#js-clubs-content')
          }
          if (!this.isDeactivated) {
            this.setClubsAroundPosition();
          }
        }
      },
      setScrollClub() {
        if (app.isMobile && this.$refs && this.$refs.mobileScrollTopContainer) {
          let container = document.getElementById('application-wrapper');
          container.addEventListener('scroll', this.checkScroll, true)
        }
      },
      setClubsAroundPosition() {
          app.prevClubsAroundPosition = this.$refs.mobileScrollTopContainer ? this.$refs.mobileScrollTopContainer.scrollTop : 0
      }
    },
    watch: {
      'userProfile.view_sensitive_clubs': function (value) {
        console.log('[Clubs List Mobile] userProfile watcher', {value})
        if (app.isApp && value === 'no') {
          this.setType('friends')
        }
      }
    },
    mounted() {
      console.log('[Clubs] Mounted')
      this.loadScrollTopButton("js-clubs-content");
      this.refresh = this.attachPullToRefresh('#js-clubs-content')
  
      this.setScrollClub()
  
      if (this.$route.query.clubId) {
        this.openClub(this.$route.query.clubId, 'club');
      }
  
      // if (this.$route.query.bangId) {
      //   this.openClub(this.$route.query.bangId, 'bang');
      // }
  
      // this.getStickyClubs()
    },
    activated() {
        if (this.isMobile) {
          app.$emit('reload-clubs')
        }
  
        this.isDeactivated = false
        this.setScrollClub()
    },
    deactivated() {
        this.isDeactivated = true;
    }
  }
  </script>
  