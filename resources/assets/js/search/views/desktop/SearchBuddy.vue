<template>
  <div class="w-app">
    <div id="application-wrapper">

      <TopBarDesktop v-if="isDesktop"/>

      <vue-custom-scrollbar class="content-wrapper search-menu" id="js-discover-content" v-on:scroll="showScrollTopButton">

        <div class="box">
          <div class="search">
            <input
                type="text"
                v-model="searchBuddyInput"
                :placeholder="trans('main_search_users')"
                name="searchInput"
                ref="searchBuddy"
                @input="searchBuddyDebounce"
                :class="{searchTagInputActive:searchTagInputActive}"
                @keypress.enter.prevent="searchBuddy()">
            <input type="submit" class="submit" @click="searchBuddy()" v-if="!searchBuddyInput.length">
            <div class="close searchClose closeSearchDesktop" @click="cleanSearch()" v-if="searchBuddyInput.length"></div>
            <div class="searchTagsParent" v-if="canShowTagsBlock">
              <vue-custom-scrollbar class="searchTagsChild">
                <div class="searchTag" v-for="tag in searchTags">
                  <a href="javascript:void(0);" @click="fillTag(tag)">#{{ tag.name }}</a>
                </div>
                <div class="searchTag" v-if="!searchTags.length && !tagsLoading">
                  <span>Tags not found</span>
                </div>
                <div class="searchTag" v-if="tagsLoading">
                  <span>...</span>
                </div>
              </vue-custom-scrollbar>
            </div>
          </div>

          <div class="pagetitle">
            <h1 v-if="usersAround.length > 0">{{ trans('search_results') }}</h1>
          </div>
        </div>
        <div class="faces catalog" v-if="isDesktop">

          <div class="face" :id="'discover-user-' + user.id" v-for="user in usersAround"
               @click="openUserModal(user.id, 2)"
               data-open2="card"
               :class="{'online': user.isOnline, 'was-online': user.wasRecentlyOnline && !user.isOnline, 'favorite': user.isFavorite, 'sensitive': user.view_sensitive_media === 'no'}">
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
      </vue-custom-scrollbar>
    </div>
  </div>
</template>

<script>
import {mapState, mapActions, mapGetters} from 'vuex';
import InfiniteLoading from 'vue-infinite-loading';
import TopBarDesktop from '@buddy/views/widgets/TopBarDesktop.vue';
import discoverModule from '@discover/module/store/type';
import $ from "jquery";
// custom scrollbar
import vueCustomScrollbar from 'vue-custom-scrollbar'
import "vue-custom-scrollbar/dist/vueScrollbar.css"
import {
  BFormTags,
} from 'bootstrap-vue'

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

      searchBuddyInput: '',
      searchTags: [],
      canShowTagsBlock:false,
      searchTagInputActive:false,
      tagsLoading:false,
    }
  },
  components: {
    InfiniteLoading,
    TopBarDesktop,
    vueCustomScrollbar,
    BFormTags,
  },
  watch: {
    searchBuddyInput: function(val) {
      this.searchTagInputActive = false;

      this.$nextTick(() => {
        this.searchTagInputActive = val.match(/\#(.+)/);
      });
    },
  },
  computed: {
    ...mapState({
      onlineFavorites: 'onlineFavorites'
    }),
  },
  methods: {
    cleanSearch() {
      this.searchBuddyInput = '';
      this.canShowTagsBlock = false;

      this.$nextTick(() => {
        setTimeout(() => {
          this.searchBuddy();
        });
      });
    },
    searchBuddyInputReaction() {
      this.searchTags = [];
      this.tagsLoading = true;
      this.canShowTagsBlock = this.searchBuddyInput.match(/\#(.+)/);

      if (this.canShowTagsBlock) {
        try {
          axios.get('/api/search_tags', {
            params: {
              searchRequest: this.searchBuddyInput,
            }
          })
              .then((response) => {
                this.searchTags = response.data.tags;
                this.tagsLoading = false;
              })
        } catch (e) {
          console.log(e);
        }
      }
    },
    fillTag(tag) {
      this.searchBuddyInput = '#'+tag.name;

      setTimeout(() => {
        this.canShowTagsBlock = false;
        this.searchBuddy(true);
      }, 200);
    },
    scrollToTop() {
      $('#js-discover-content').addClass('disable-scroll');
      $('#js-discover-content').bind('touchmove', function(e){e.preventDefault()})
      let container = document.getElementById('js-discover-content');
      container.scrollTo({
        top: 0,
      })
    },
    showScrollTopButton(event) {
      let currentScroll = event.target.scrollTop;
      this.showScrollTop = currentScroll >= 370;
    },
    searchBuddyDebounce: _.debounce(function (input) {
      this.searchBuddyInputReaction();

      if (!this.canShowTagsBlock) {
        this.$store.dispatch(discoverModule.actions.filter.set, {
          key: 'filterName',
          value: input.target.value,
          queueRefresh: true
        });
        this.resetUsersAround();
      }
    }, 1000),
    searchBuddy(force=false) {
      if (!this.canShowTagsBlock || force) {
        this.$store.dispatch(discoverModule.actions.filter.set, {
          key: 'filterName',
          value: this.searchBuddyInput,
          queueRefresh: true
        });
        this.resetUsersAround()
      }
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
      $('#js-discover-content').removeClass('disable-scroll');
      this.scroll = 0;
      this.showScrollTop = false;
    }
  },
  mounted() {
    let container = document.getElementById('application-wrapper');
    container.addEventListener('scroll', this.scrollEvent, true);

    let url = new URL(location)
    let params = new URLSearchParams(url.search);

    if (params.has('q')) {
      let q = params.get('q')

      this.searchBuddyInput = q;
      this.searchBuddy();
    }
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
.closeSearchDesktop {
  top:15px !important;
}
</style>
