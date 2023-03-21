<template>
  <div class="w-app">
    <div id="application-wrapper" v-on:scroll="showScrollTopButton">

      <TopBarDesktop v-if="isDesktop"/>

      <div class="content-wrapper">

        <div class="secondary-menu search-menu">
          <div class="secondary-menu-header" v-if="!isDesktop">
            <i class="back" @click="goTo('/discover')"></i>
          </div>
          <div class="secondary-menu-body" id="secondary-menu-body-scroll">
            <div class="box">
              <div class="search">
                <input
                    type="text"
                    :placeholder="trans('main_search_users')"
                    name="searchInput"
                    ref="searchBuddy"
                    @input="searchBuddyDebounce"
                    @keypress.enter.prevent="searchBuddy()">
                <input type="submit" class="submit" @click="searchBuddy()">
              </div>
              <div class="title" v-if="usersAround.length > 0">{{ trans('search_results') }}</div>
            </div>
            <div class="wrap" id="wrap-scroll">
              <div class="faces" id="js-discover-content" v-if="!isDesktop">
                <div class="face" :id="'discover-user-' + user.id" v-for="user in usersAround"
                     v-on:click="goToProfile(user)"
                     data-open2="card"
                     :class="{'online': user.isOnline, 'was-online': user.wasRecentlyOnline && !user.isOnline, 'favorite': user.isFavorite, 'sensitive': user.view_sensitive_media === 'no'}">
                  <div class="img" :style="{'background': `url(${user.photo_small}) no-repeat center / cover`}"></div>
                  <div class="number-indicator" v-if="user.unreadMessagesCount">{{ user.unreadMessagesCount }}</div>
                  <div class="details">
                    <div class="name notranslate">{{ user.name }}</div>
                  </div>
                </div>
              </div>

              <div class="faces" id="js-discover-content" v-if="isDesktop">

                <div class="face" :id="'discover-user-' + user.id" v-for="user in usersAround"
                     @click="openUserModal(user.id, 2)"
                     data-open2="card"
                     :class="{'online': user.isOnline, 'was-online': user.wasRecentlyOnline && !user.isOnline, 'favorite': user.isFavorite, 'has-videos': user.has_videos, 'sensitive': user.view_sensitive_media === 'no'}">
                  <div class="img" :style="{'background': `url(${user.photo_small}) no-repeat center / cover`}"></div>
                  <div class="number-indicator" v-if="user.unreadMessagesCount">{{ user.unreadMessagesCount }}</div>
                  <div class="details">
                    <div class="name notranslate">{{ user.name }}</div>
                  </div>
                </div>
              </div>

              <infinite-loading
                  ref="infiniteLoadingDiscover"
                  @infinite="loadUsersAround"
                  force-use-infinite-wrapper="#js-discover-content"
                  spinner="bubbles">
                <span slot="no-more" v-if="!usersNextPageCount">{{ trans('no_more_users_around') }}</span>
                <span slot="no-more" v-if="usersNextPageCount"></span>
                <span slot="no-results"></span>
              </infinite-loading>
              <transition name="fade">
                <div v-show="showScrollTop" class="scroll_top-button"
                     @click="scrollToTop()" :title="trans('arrow_scroll_top')">
                  <svg class="icon icon-arrow_up">
                    <use v-bind:xlink:href="symbolsSvgUrl('icon-arrow_up')"></use>
                  </svg>
                </div>
              </transition>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mapState, mapActions, mapGetters} from 'vuex';
import InfiniteLoading from 'vue-infinite-loading';

import TopBarDesktop from '@buddy/views/widgets/TopBarDesktop.vue';

import discoverModule from '@discover/module/store/type';
import $ from "jquery";

export default {
  mixins: [
    require('@general/lib/mixin').default,
    require('@general/lib/mixin-discover').default
  ],
  props: ['tab'],
  data() {
    return {
      scroll: 0,
      section: 'search',
      awaitingSearch: false,
      showScrollTop: false,
      searchBuddyInput: ''
    }
  },
  components: {
    InfiniteLoading,
    TopBarDesktop
  },
  computed: {
    ...mapState({
      onlineFavorites: 'onlineFavorites'
    }),
  },
  methods: {
    scrollToTop() {
      $('#application-wrapper').addClass('disable-scroll');
      $('#application-wrapper').bind('touchmove', function(e){e.preventDefault()})
      let container = document.getElementById('application-wrapper');
      container.scrollTo({
        top: 0
      })
    },
    showScrollTopButton(event) {
        let currentScroll = event.target.scrollTop;
        this.showScrollTop = currentScroll >= 370;
    },
    searchBuddyDebounce: _.debounce(function (input) {
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterName',
        value: input.target.value,
        queueRefresh: true
      });
      this.resetUsersAround();
    }, 1000),
    searchBuddy() {
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterName',
        value: this.searchInput,
        queueRefresh: true
      });
      this.resetUsersAround()
    },
    goToProfile(user) {
      this.$router.push({name: 'user', params: {userToken: user.link || user.id}})
    },
    deleteFilterName() {
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterName',
        value: '',
        queueRefresh: true
      });
      this.$refs.searchBuddy.value = '';
    },
    scrollEvent: _.debounce(function (event) {
        let currentScroll = event.target.scrollTop;
        let scrollHeight = event.target.scrollHeight;
        let clientHeight = event.target.clientHeight;

        if (currentScroll === 0) {
            let container = $('#application-wrapper');
            container.unbind('touchmove');
            container.removeClass('disable-scroll');
        }

        if (currentScroll + clientHeight === scrollHeight && this.usersNextPageCount) {
            this.$refs.infiniteLoadingDiscover.stateChanger.reset();
        }

        this.showScrollTop = currentScroll >= 300;
    }, 100),
    removeDisableScroll() {
      $('#application-wrapper').removeClass('disable-scroll');
      this.scroll = 0;
      this.showScrollTop = false;
    }
  },
  mounted() {
    let container = document.getElementById('application-wrapper');
    container.addEventListener('scroll', this.scrollEvent, true);
  },
  deactivated() {
    this.deleteFilterName();
    this.removeDisableScroll();
  },
  destroyed() {
    this.removeDisableScroll();
    this.deleteFilterName();
  }
}
</script>
<style>
  .disable-scroll {
      overflow: hidden !important;
  }
</style>