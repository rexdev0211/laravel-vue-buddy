<template>
  <div>
    <div class="w-app">
      <div id="application-wrapper">

        <TopBarDesktop/>

<!--        <modal name="welcome-message-modal"-->
<!--               :width="400"-->
<!--               :height="600"-->
<!--               :adaptive="true"-->
<!--               @closed="closedWelcomeModal">-->
<!--            <div class="welcome-modal-main">-->
<!--              <div class="close"-->
<!--                   @click.prevent="hideModal">-->
<!--              </div>-->
<!--              <div class="welcome-modal-top-icon">-->
<!--                <img src="/main/img/icons/button-calendar-green.png" class="calendar">-->
<!--              </div>-->
<!--              <div class="welcome-modal-title">{{ trans('party_on_buddies1') }}<br>{{ trans('party_on_buddies2') }}</div>-->
<!--              <div class="welcome-modal-content">-->
<!--                <p>{{ trans('party_on_text1') }}</p>-->
<!--                <p>{{ trans('party_on_text2') }}</p>-->
<!--              </div>-->
<!--              <div class="like">-->
<!--                <img src="/main/img/icons/heart-green.png">-->
<!--              </div>-->
<!--            </div>-->
<!--        </modal>-->

        <vue-custom-scrollbar class="content-wrapper" id="js-discover-content" ref="discoverScrollContent" v-on:scroll="prepareScroll($event.target)">
          <div class="favorites" v-on:mouseover="buttonControl(true)" v-on:mouseleave="buttonControl(false)" v-if="onlineFavorites.length">
            <div class="h1">{{ trans('favorites') }}</div>
            <div class="faces-container" style="display: flex; align-items: center">
              <vue-horizontal :button="showHorizontalButtons" @prev="onBack" @next="onNext" ref="faces" class="faces">
                <div class="face" :key="index" v-for="(favorite, index) in onlineFavorites"
                     :dusk="'favorite-online-'+favorite.id" href="javascript:void(0)"
                     @click="openUserModal(favorite.id, 1)"
                     :class="{'online': favorite.isOnline, 'was-online': favorite.wasRecentlyOnline && !favorite.isOnline, 'default-photo': favorite.photo_small === '/assets/img/default_180x180.jpg', 'sensitive': favorite.photo_small === '/assets/img/toohot_180x180.jpg'}">
                  <div class="img" :style="{'background': `url(${favorite.photo_small}) no-repeat center / cover`}"></div>
                  <div class="details">
                    <div class="name notranslate">{{ favorite.name }}</div>
                  </div>
                </div>
              </vue-horizontal>
            </div>
          </div>

            <div class="pagetitle">
              <h1>{{ trans(filterBuddies) }}</h1>
            </div>
            <div class="faces catalog" :class="{'list': viewType === 'list'}">

              <div class="face" :id="'discover-user-' + user.id" v-for="user in usersAround"
                   @click="openUserModal(user.id, 2)"
                   data-open2="card"
                   :class="{'online': user.isOnline, 'was-online': user.wasRecentlyOnline && !user.isOnline, 'favorite': user.isFavorite, 'sensitive': user.photo_small === '/assets/img/toohot_180x180.jpg', 'default-photo': user.photo_small == '/assets/img/default_180x180.jpg'}">
                <div class="img" :style="{'background': `url(${user.photo_small}) no-repeat center / cover`}"></div>
                <div class="has-videos" v-if="user.has_videos"></div>
                <div class="number-indicator" v-if="user.unreadMessagesCount">{{ user.unreadMessagesCount }}</div>
                <div class="details">
                  <div class="name notranslate">{{ user.name }}</div>
                  <div class="parameters-box">
                    <div class="parameters">
                      <span v-if="user.age">{{ user.age }}</span>
                      <span v-if="user.height">{{ formatHeight(user.height, false) }}</span>
                      <span v-if="user.weight">{{ formatWeight(user.weight, false) }}</span>
                      <span v-if="user.position">{{ transPosition(user.position, true) }}</span>
                    </div>
                    <div class="distance" v-if="user.id != authUserId">{{ getDistanceString(user, false) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <infinite-loading
                ref="infiniteLoadingDiscover"
                @infinite="loadUsersAround"
                force-use-infinite-wrapper="#js-discover-content"
                spinner="bubbles">
              <span slot="no-more">{{ trans('no_more_users_around') }}</span>
              <span slot="no-more"></span>
              <span slot="no-results">{{ trans('no_more_users_around') }}</span>
            </infinite-loading>
            <transition name="fade">
              <div v-show="showScrollTop" class="scroll_top-button"
                   @click="scrollToTop()" :title="trans('arrow_scroll_top')">
                <svg class="icon icon-arrow_up">
                  <use v-bind:xlink:href="symbolsSvgUrl('icon-arrow_up')"></use>
                </svg>
              </div>
            </transition>
        </vue-custom-scrollbar>
        <HideChats />
      </div><!--#application-wrapper-->
    </div><!--w-app-->
  </div>
</template>

<script>
import {mapState, mapActions, mapGetters} from 'vuex';
import InfiniteLoading from 'vue-infinite-loading';

import TopBarDesktop from '@buddy/views/widgets/TopBarDesktop.vue';
import DiscoverDesktopFilterReveals from '@discover/views/widgets/DiscoverDesktopFilterReveals.vue';

import discoverModule from '@discover/module/store/type';

import HideChats from "@buddy/views/widgets/HideChats.vue";

// custom scrollbar
import vueCustomScrollbar from 'vue-custom-scrollbar'
import "vue-custom-scrollbar/dist/vueScrollbar.css"

// Vue horizontal for favorites block
import VueHorizontal from 'vue-horizontal';

export default {
  data() {
    return {
      visibleFilter: false,
      showScrollTop: false,
      settings: {
        suppressScrollY: false,
        suppressScrollX: false,
        wheelPropagation: false
      },
      favoritesSettings: {
          showPrevButton: false,
          showNextButton: false,
          scrollLeftCount: 1000
      },
      favoritesPage: 0,
      windowWidth: window.innerWidth,
      showHorizontalButtons: false,
    }
  },
  mixins: [
    require('@general/lib/mixin').default,
    require('@general/lib/mixin-discover').default
  ],
  components: {
    InfiniteLoading,
    TopBarDesktop,
    DiscoverDesktopFilterReveals,
    HideChats,
    vueCustomScrollbar,
    VueHorizontal,
  },
  computed: {
    ...mapState({
      onlineFavorites: 'onlineFavorites',
      user: 'profile',
    }),
  },
  methods: {
    ...mapActions([
      'trySwitchDiscreetMode',
    ]),
    // showPartiesOn(slot) {
    //   if (slot) {
        //this.showModal();
      // }
    // },
    // closedWelcomeModal() {
    //   this.updateUserModalShown({
    //     'is_guide_modal_shown': true,
    //   });
    // },
    async updateUserModalShown(payload) {
      this.$store.commit('updateUser', payload)
      await axios.post('/api/updateUser', payload)
      console.log(`[User] Modal settings setting updated`, { payload })
    },
    showModal () {
      this.$modal.show('welcome-message-modal');
    },
    hideModal () {
      this.$modal.hide('welcome-message-modal');
    },
    buttonControl(show) {
        this.showHorizontalButtons = show
    },
    onNext() {
        this.favoritesPage += 1
    },
    onBack() {
      this.favoritesPage -= 1
    },
    scrollTo(element, scrollPixels) {
        element.scroll({
          left: scrollPixels,
          top: 0,
          // behavior: 'smooth' DO NOT UNCOMMENT ! IT'S NOT WORKING ON FIREFOX.
        })

        if (this.favoritesPage > 0) {
          this.favoritesSettings.showPrevButton = true;
        } else if (this.favoritesPage === 0) {
          this.favoritesSettings.showPrevButton = false;
        }

        if (element.scrollWidth <= (element.offsetWidth + scrollPixels)) {
            this.favoritesSettings.showNextButton = false;
        } else {
            this.favoritesSettings.showNextButton = true
        }

    },
    checkProFilters() {
      if (!this.userIsPro) {
        const proFilters = this.$store.getters[discoverModule.getters.filter.proFilters];

        proFilters.forEach((value) => {
          let key = `${value}Values`;

          this.$store.dispatch(discoverModule.actions.filter.remove, {
            key,
            refresh: true
          });
        })
      }
    },
    scrollToTop() {
      let container = document.getElementById('js-discover-content');
      container.scrollTo({
        top: 0,
      })
    },
    resetSearchUsers() {
      this.searchInput = ''
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterName',
        value: '',
        queueRefresh: true
      })
    },
    setScrollPosition() {
      app.prevUsersAroundPosition = this.$refs.discoverScrollContent.scrollTop
    },
    setfFlterType(value) {
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterType',
        value,
        refresh: true
      })
    },
    setScrollToDiscover() {
      let container = document.getElementById('application-wrapper');
      if (container) {
        container.addEventListener('scroll', this.checkScrollDebounce, true);
      }
    },
    prepareScroll(eventTarget) {
      let currentScroll = eventTarget.scrollTop;
      this.handleDiscoverScroll({})
      if (currentScroll === 0) {
        $('#application-wrapper').unbind('touchmove')
        setTimeout(() => {
          let container = $('#application-wrapper')
          container.removeClass('disable-scroll');
        }, 100);
      }

      if (app.isDesktop) {
        this.showScrollTop = currentScroll >= 370;

      }
    },
    checkScrollDebounce: _.debounce(function (event) {
      if (event) {
        let currentScroll = event.target.scrollTop;
        // let scrollHeight = event.target.scrollHeight;
        // let clientHeight = event.target.clientHeight;

        this.scroll = currentScroll

        if (currentScroll > 5) {
          if (this.pullToRefreshInstance) {
            this.pullToRefreshInstance.destroy()
          }
        } else {
          this.pullToRefreshInstance = this.attachPullToRefresh('#js-events-content')
        }

        // if (currentScroll + clientHeight === scrollHeight && this.usersNextPageCount) {
        //   this.$refs.infiniteLoadingDiscover.stateChanger.reset();
        // }

        this.showScrollTop = currentScroll >= 370;
      }
    }, 100),
    checkWidth() {
        const element = this.$refs.faces;
        const container = document.querySelector('.faces-container')

        if (element && container) {
          if (element.scrollWidth - (container.offsetWidth + element.scrollLeft)) {
            this.favoritesSettings.showNextButton = true;
          } else {
            this.favoritesSettings.showNextButton = false;
          }

          if (element.scrollLeft > 0) {
            this.favoritesSettings.showPrevButton = true;
          } else {
            this.favoritesSettings.showPrevButton = false;
          }
        }
    },
    onResize() {
        this.windowWidth = window.innerWidth
    }
  },
  created() {
    console.log('[Discover] Created')
    if (app.isDesktop) {
      this.setScrollToDiscover();
    }
    // app.$on('reload-discover', this.resetUsersAround);
    // app.$on('show-parties-on', this.showPartiesOn);
  },
  mounted() {
    console.log('[Discover] Mounted')

    let v = this
    /*$(document).ready(function() {
        $('#online__favorites').scrollToFixed({
            bottom: 0
        });
    });*/

    this.loadScrollTopButton();

    if (app.isDesktop) {
      this.setScrollToDiscover();
    }

    this.$nextTick(() => {
        window.addEventListener('resize', this.onResize);
    })

    const discoverScrollContent = document.getElementById('js-discover-content')

    if (typeof discoverScrollContent !== 'undefined') {
      discoverScrollContent.addEventListener('scroll', function () {
        v.setScrollPosition();
      });
    }

    if (this.$route.query.user) {
      this.openUserModal(this.$route.query.user, 4)
    }
  },
  watch: {
      userIsPro() {
         this.checkProFilters();
         this.resetUsersAround();
      },
      windowWidth() {
          this.checkWidth()
      },
      onlineFavorites() {
          this.checkWidth()
      },
  },
  activated() {
    console.log('[Discover] Activated')
    if (app.isDesktop) {
      this.setScrollToDiscover();
    }

    if (this.$route.meta.back.match(/\/search/)) {
      this.resetUsersAround();
    }

    // app.$emit('reload-discover')
    app.$on('reload-discover', this.resetUsersAround)
  },
  deactivated() {
    const visibleFilter = this.visibleFilter;
    this.$store.dispatch('updateSidebarVisibility', {index: 'filter', visibleFilter})
    this.closeUserModal();
  },
  destroyed() {
    const visibleFilter = this.visibleFilter;
    this.$store.dispatch('updateSidebarVisibility', {index: 'filter', visibleFilter})
    app.$off('reload-discover')
  }
}
</script>

<style lang="scss">
.ps__rail-y {
  z-index: 1015 !important;
}

.v-hl-btn-next {
  transform: translateX(10%) !important;
}

.v-hl-btn-prev {
  transform: translateX(-10%) !important;
}

.welcome-modal-main {
  background:url('/assets/img/welcome-modal-background.jpg') center center no-repeat;
  width:100%;
  height:100%;
  position: absolute;
  background-size: cover;
}
.welcome-modal-main .close {
  filter: invert(100%);
}
.welcome-modal-title {
  text-align: center;
  font-size:52px;
  font-weight: bold;
  margin-top:10%;
  color:white;
  font-family: Arial, Baskerville, monospace;
  line-height: 95%;
}
.welcome-modal-content {
  padding-left:10%;
  padding-right:10%;
  margin-top:5%;
  text-align: center;
}
.welcome-modal-content p:first-child {
  margin-top:25px;
}
.welcome-modal-content p {
  margin:15px 0 15px 0;
  padding:0;
  font-weight: bold;
  line-height: 110%;
  font-size:20px;
  color:white;
  font-family: Arial, Baskerville, monospace;
}
.welcome-modal-top-icon {
  text-align: center;
  width:100%;
  margin-top:15%;
}
.welcome-modal-top-icon .calendar {
  max-width:50px;
  max-height: 50px;
  filter: drop-shadow(0px 2px 4px rgba(0, 0, 0, 0.5));
}
.welcome-modal-main .like {
  margin-top:10%;
  text-align: center;
}
.welcome-modal-main .like img {
  width:33px;
  height:30px;
}
.vm--modal {
  background: rgba(0, 0, 0, 0.4) !important;
  box-shadow: 10px 10px 10px rgb(0 0 0 / 50%) !important;
}

</style>