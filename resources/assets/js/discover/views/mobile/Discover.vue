<template>
    <div class="w-root off-canvas-wrapper">
        <div class="off-canvas-content w-views" data-off-canvas-content>
<!--          <div class="mobilePopup">-->
<!--            <modal name="welcome-message-modal"-->
<!--                   :width="380"-->
<!--                   :height="580"-->
<!--                   :adaptive="true"-->
<!--                   @closed="closedWelcomeModal">-->
<!--              <div class="welcome-modal-main">-->
<!--                <div class="close"-->
<!--                     @click.prevent="hideModal">-->
<!--                </div>-->
<!--                <div class="welcome-modal-top-icon">-->
<!--                  <img src="/main/img/icons/button-calendar-green.png" class="calendar">-->
<!--                </div>-->
<!--                <div class="welcome-modal-title">{{ trans('party_on_buddies1') }}<br>{{ trans('party_on_buddies2') }}</div>-->
<!--                <div class="welcome-modal-content">-->
<!--                  <p>{{ trans('party_on_text1') }}</p>-->
<!--                  <p>{{ trans('party_on_text2') }}</p>-->
<!--                </div>-->
<!--                <div class="like">-->
<!--                  <img src="/main/img/icons/heart-green.png">-->
<!--                </div>-->
<!--              </div>-->
<!--            </modal>-->
<!--          </div>-->

            <vue2-gesture :type="'swipeLeft'" :call="handleGesture.bind(this, 'swipeLeft')">
                <vue2-gesture :type="'swipeRight'" :call="handleGesture.bind(this, 'swipeRight')">
                    <div id="application-wrapper">
                        <transition :name="'fade'" mode="out-in">
                            <header v-if="navbarVisible">
                                <div class="user-box" v-if="profile.id"
                                    @click="toggleProfileSidebar(null)"
                                    :class="{'online': !discreetModeEnabled}">
                                    <div class="img" :style="{'background': `url(${defaultPhoto}) no-repeat center / cover`}"></div>
                                </div>
                                <div class="buttons">
                                    <div class="button mask"
                                        v-if="userIsPro"
                                        :class="{'active': discreetModeEnabled}"
                                        @click="trySwitchDiscreetMode">
                                    </div>
                                    <div dusk="discover-filters" id="filter"
                                        :class="{'activated': enabledFiltersCount}"
                                        @click="toggleFilterSidebar(null)"
                                         v-if="!searchBuddyInput.length"
                                        class="button filter">
                                        <span class="quantity" v-if="enabledFiltersCount">{{ enabledFiltersCount }}</span>
                                    </div>
                                    <div dusk="notifications-page" id="visits"
                                        class="button visits"
                                        :class="{'active': tab === 'chat', 'notificated': userHasNotifications}"
                                        @click="goTo('/notifications')">
                                        <div class="notificated-icon" v-if="userHasNotifications"></div>
                                    </div>
                                </div>
                                <button class="hamburger filter-button"
                                        type="button"
                                        @click="toggleFilterSidebar(null)">
                                    <span class="hamburger-box">
                                        <span class="hamburger-inner"></span>
                                    </span>
                                </button>
                            </header>
                        </transition>

                        <div class="mobileSidebarHolder positionLeft"
                             :class="{'active': sidebar.profile.visible}">
                            <div class="mobileSidebarHide" @click="toggleProfileSidebar(false)"></div>
                            <div class="mobileSidebar">
                                <ProfileMenu/>
                            </div>
                        </div>

                        <div class="mobileSidebarHolder positionRight"
                             :class="{'active': sidebar.filter.visible}">
                            <div class="mobileSidebarHide" @click="toggleFilterSidebar(false)"></div>
                            <div class="mobileSidebar">
                                <FiltersForms v-on:disableSwipe="setDisableSwipe"/>
                            </div>
                        </div>

                        <div id="content-wrapper-wrapper" class="content-wrapper" @scroll="prepareScroll($event)" ref="mobileScrollTopContainer">
                          <div id="js-search-content" class="content-wrapper search-menu" v-if="showSearchInput">
                            <div class="box">
                              <div class="search">
                                <input
                                    type="text"
                                    :placeholder="trans('main_search_users')"
                                    v-model="searchBuddyInput"
                                    name="searchInput"
                                    ref="searchBuddy"
                                    @input="searchBuddyDebounce"
                                    :class="{searchTagInputActive:searchTagInputActive}"
                                    @keypress.enter.prevent="searchBuddy()">
                                <input type="submit" class="submit" @click="searchBuddy()" v-if="!searchBuddyInput.length">
                                <div class="close searchClose" @click="cleanSearch()" v-if="searchBuddyInput.length"></div>
                                <div class="searchTagsParent" v-if="canShowTagsBlock">
                                  <vue-custom-scrollbar class="searchTagsChild discoverSearchTagsChild">
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
                            </div>
                          </div>
                          <div class="clearBothDiscoverSearch" v-if="showSearchInput"></div>


                            <div class="favorites" v-if="onlineFavorites.length && !searchBuddyInput.length">
                                <div class="h1">{{ trans('favorites') }}</div>
                                <div class="faces" @scroll="onScroll">
                                    <div class="face" v-for="favorite in onlineFavorites"
                                        :dusk="'favorite-online-'+favorite.id" href="javascript:void(0)"
                                        v-on:click="goToProfile(favorite)"
                                        :class="{'online': favorite.isOnline, 'was-online': favorite.wasRecentlyOnline && !favorite.isOnline, 'default-photo': favorite.photo_small === '/assets/img/default_180x180.jpg', 'sensitive': favorite.photo_small === '/assets/img/toohot_180x180.jpg'}">
                                        <div class="img" :style="{'background': `url(${favorite.deleted_at ? trashIcon : favorite.photo_small}) no-repeat center / cover`}"></div>
                                        <div class="details">
                                            <div class="name notranslate">{{ favorite.name }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                          <div class="pagetitle">
                            <h1 v-if="searchBuddyInput.length">{{ trans('search_results') }}</h1>
                            <h1 v-else-if="usersAround.length">{{ trans(filterBuddies) }}</h1>
                          </div>

                            <div id="js-discover-content" class="faces catalog" :class="{'list': viewType === 'list'}">
                                <div class="face" :id="'discover-user-' + user.id" v-for="user in usersAround"
                                    v-on:click="goToProfile(user)"
                                    data-open2="card"
                                    :ref="'userFace'+user.id"
                                    :class="{'online': user.isOnline, 'was-online': user.wasRecentlyOnline && !user.isOnline, 'favorite': user.isFavorite, 'sensitive': user.photo_small === '/assets/img/toohot_180x180.jpg', 'default-photo': user.photo_small == '/assets/img/default_180x180.jpg'}">
                                    <div class="img" :style="{'background': `url(${user.deleted_at ? trashIcon : user.photo_small}) no-repeat center / cover`}"></div>
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
                              force-use-infinite-wrapper="#content-wrapper-wrapper"
                              direction="bottom"
                              spinner="bubbles">
                            <span slot="no-more" v-if="!usersNextPageCount">{{ trans('no_more_users_around') }}</span>
                            <span slot="no-more" v-if="usersNextPageCount"></span>
                            <span slot="no-results">{{ trans('no_more_users_around') }}</span>
                          </infinite-loading>
                          <transition name="fade">
                            <div v-show="showScrollTop" class="scroll_top-button"
                                 @click="scrollToTop()" :title="trans('arrow_scroll_top')">
                                <svg class="icon icon-arrow_up"><use v-bind:xlink:href="symbolsSvgUrl('icon-arrow_up')"></use></svg>
                            </div>
                          </transition>
                        </div>
                        <AddHomeScreen showOn="discover"/>
                        <BottomBar tab="discover"/>
                    </div><!--#application-wrapper-->
                </vue2-gesture>
            </vue2-gesture>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import {mapActions, mapGetters, mapState} from 'vuex';
    import InfiniteLoading from 'vue-infinite-loading';

    import BottomBar from '@buddy/views/widgets/BottomBar.vue';
    import AddHomeScreen from '@buddy/views/widgets/AddHomeScreen.vue';
    import FiltersForms from "@discover/views/widgets/FiltersForms.vue";
    import ProfileMenu from "@profile/views/widgets/ProfileMenu.vue";

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    import discoverModule from '@discover/module/store/type';
    import $ from 'jquery'
    import _ from "lodash";
    import Vue from "vue";

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-discover').default
        ],
        props: ['tab'],
        data() {
            return {
                scroll: 0,
                pullToRefreshInstance: null,
                navbarVisible: true,
                disableSwipe: false,
                showScrollTop: false,
                scrollEvent: null,
                disableScrollTop: false,
                scrollTimeout: null,
                currentFilter: null,

                showSearchInput: true,

                searchBuddyInput: '',
                searchTags: [],
                canShowTagsBlock:false,
                searchTagInputActive:false,
                tagsLoading:false,
            }
        },
        components: {
            FiltersForms,
            ProfileMenu,
            BottomBar,
            InfiniteLoading,
            AddHomeScreen,
            vueCustomScrollbar,
        },
        computed: {
            ...mapState({
                sidebar: state => state.sidebar,
                onlineFavorites: 'onlineFavorites',
                profile: 'profile',
                user: 'profile',
                haveUnblockedUsers: 'haveUnblockedUsers',
            }),
            ...mapGetters([
                'discreetModeEnabled',
                'userHasNotifications'
            ]),
            defaultPhoto() {
                let avatars = this.profile.avatars
                if (!avatars.adult && (avatars.default?.rejected || avatars.default?.pending)) {
                  return '/assets/img/default_180x180.jpg';
                } else {
                  return _.get(avatars, 'merged.photo_small', '/assets/img/default_180x180.jpg')
                }
            },
            debounceGetScroll: function () {
                return _.debounce(this.checkScroll, 100);
            }
        },
        methods: {
            setScrollFromTag() {
              let url = new URL(location)
              let params = new URLSearchParams(url.search);

              if (params.has('tag')) {
                let tag = params.get('tag')
                let ref = this.$refs['userFace'+tag];

                if (ref && ref.length) {
                  ref = ref[0];

                  if (ref) {
                    console.log('REF DATA');
                    console.log(ref);
                    ref.scrollIntoView();
                  }
                }
              }
            },
            cleanSearch() {
              this.searchBuddyInput = '';
              this.canShowTagsBlock = false;

              this.$nextTick(() => {
                setTimeout(() => {
                  this.searchBuddy();
                });
              });
            },
            ...mapActions([
                'trySwitchDiscreetMode'
            ]),
            // showPartiesOn(slot) {
            //   if (slot) {
            //     this.showModal();
            //   }
            // },
            // closedWelcomeModal() {
            //   this.updateUserModalSohwn({
            //     'is_guide_modal_shown': true,
            //   });
            // },
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
                  refresh: true
                })
              }
            },
            async updateUserModalSohwn(payload) {
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

              Vue.nextTick(() => {
                this.setScrollFromTag();
              })
            },
            setDisableSwipe(value) {
                this.disableSwipe = value;
            },
            onScroll() {
                const vm = this;
                vm.setDisableSwipe(true);
                clearTimeout(vm.scrollTimeout);

                vm.scrollTimeout = setTimeout(function () {
                    vm.setDisableSwipe(false)
                }, 300);
            },
            toggleProfileSidebar(value) {
                if (value === null){
                    value = !this.sidebar.profile.visible
                    if (value) {
                        this.closeFilterSidebar(false)
                    }
                }
                this.$store.dispatch('updateSidebarVisibility', {index: 'profile', value})
            },
            closeFilterSidebar(value) {
              this.$store.dispatch('updateSidebarVisibility', {index: 'filter', value})
            },
            toggleFilterSidebar(value) {
                if (value === null){
                    value = !this.sidebar.filter.visible
                    if (value) {
                        this.toggleProfileSidebar(false)
                    }
                }

                this.$store.dispatch('updateSidebarVisibility', {index: 'filter', value})

                let currentFilter = {}

                let defaultFilters = this.$store.getters[discoverModule.getters.filter.defaultFilter];
                let filter = this.$store.getters[discoverModule.getters.filter.get]

                for (let defaultFilter in defaultFilters) {
                    if (defaultFilter === `${defaultFilter}Values`) {
                        currentFilter[defaultFilter] = filter(`${defaultFilter}Values`)
                    } else {
                        currentFilter[defaultFilter] = filter(defaultFilter)
                    }
                }

                let isChanged = JSON.stringify(this.currentFilter) !== JSON.stringify(currentFilter)

                if (isChanged) {
                  this.$store.dispatch(discoverModule.actions.users.setRefreshQueued, true)
                  this.resetUsersAround()
                  this.currentFilter = currentFilter
                }
            },
            handleGesture(str, e) {
                if (this.disableSwipe) {
                    return;
                }

                if (str == 'swipeLeft') {
                    if (this.sidebar.profile.visible) {
                        this.toggleProfileSidebar(false)
                    } else {
                        this.toggleFilterSidebar(true)
                    }
                }
                if (str == 'swipeRight') {
                    if (this.sidebar.filter.visible) {
                        this.toggleFilterSidebar(false)
                    } else {
                        this.toggleProfileSidebar(true)
                    }
                }
            },
            goToProfile(user) {
              this.goTo('/user/' + user.id)
              // this.$router.push({ hash: '#userModal='+user.id })
              //this.openUserMobileModal(user.id, 2)
            },
            scrollToTop() {
                if (!this.disableScrollTop) {
                  $('#content-wrapper-wrapper').addClass('disable-scroll');
                  $('#application-wrapper').removeClass('scroll-content');
                  $('#content-wrapper-wrapper').bind('touchmove', function(e){e.preventDefault()})
                  let container = document.getElementById('content-wrapper-wrapper');
                  container.scrollTo({
                    top: 0,
                  })
                }
            },
            prepareScroll(event) {
                let currentScroll = event.target.scrollTop;

                if (currentScroll === 0) {
                    $('#content-wrapper-wrapper').unbind('touchmove')
                    let container = $('#content-wrapper-wrapper')
                    container.removeClass('disable-scroll');
                }

                if (app.isMobile) {
                  $('#application-wrapper').addClass('scroll-content');
                  this.setUsersAroundPosition();
                  this.showScrollTopButton(event);
                  this.scrollEvent = event;
                  this.checkScroll();
                }
            },
            showScrollTopButton(event) {
                let currentScroll = event.target.scrollTop;
                this.showScrollTop = currentScroll >= 370;
            },
            checkScroll() {
              const event = this.scrollEvent;

              if (event) {
                let currentScroll = event.target.scrollTop;
                this.scroll = currentScroll

                if (currentScroll > 5) {
                  if (this.pullToRefreshInstance) {
                    this.pullToRefreshInstance.destroy()
                  }
                } else {
                  this.pullToRefreshInstance = this.attachPullToRefresh('#js-discover-content')
                }

                this.showScrollTop = currentScroll >= 370;
              }
            },
            checkScrollDebounce: _.debounce(function (event) {
                if (event) {
                  let currentScroll = event.target.scrollTop;
                  let scrollHeight = event.target.scrollHeight;
                  let clientHeight = event.target.clientHeight;

                  this.scroll = currentScroll

                  if (currentScroll > 5) {
                    if (this.pullToRefreshInstance) {
                      this.pullToRefreshInstance.destroy()
                    }
                  } else {
                    this.pullToRefreshInstance = this.attachPullToRefresh('#js-discover-content')
                  }

                  // if (currentScroll + clientHeight === scrollHeight) {
                  //   this.$refs.infiniteLoadingDiscover.stateChanger.reset();
                  // }

                  this.showScrollTop = currentScroll >= 370;
                }
            }, 100),
            setScrollToDiscover() {
                let container = document.getElementById('application-wrapper');
                container.addEventListener('scroll', this.checkScrollDebounce, true);
            },
            setUsersAroundPosition() {
                app.prevUsersAroundPosition = this.$refs.mobileScrollTopContainer ? this.$refs.mobileScrollTopContainer.scrollTop : 0
            },
            setMode(mode) {
                this.$store.dispatch(discoverModule.actions.filter.set, {
                    key: 'filterType',
                    value: mode,
                    refresh: true
                })
            },
            pullRefresh(){
                this.$store.dispatch(discoverModule.actions.users.setRefreshQueued, true)
                this.resetUsersAround()
                // this.showSearchInput = true
            },
            removeDisableScroll() {
                $('#application-wrapper').removeClass('disable-scroll');
                this.scroll = 0;
                this.showScrollTop = false;
            }
        },
        watch: {
            userIsPro() {
              this.checkProFilters();
              this.resetUsersAround();
            },
            searchBuddyInput: function(val) {
              this.searchTagInputActive = false;

              this.$nextTick(() => {
                setTimeout(() => {
                  this.searchTagInputActive = val.match(/\#(.+)/);
                }, 50);
              });
            },
        },
        created(){
            console.log('[Discover] Created')
            app.$on('reload-discover', this.resetUsersAround);
            // app.$on('show-parties-on', this.showPartiesOn)
        },
        mounted() {
            console.log('[Discover] Mounted');

            this.pullToRefreshInstance = this.attachPullToRefresh('#js-discover-content')

            let v = this

            if (app.isDesktop) {
                this.setScrollToDiscover();
            }

            let currentFilter = {};
            let defaultFilters = this.$store.getters[discoverModule.getters.filter.defaultFilter];
            let filter = this.$store.getters[discoverModule.getters.filter.get]

            for (let defaultFilter in defaultFilters) {
                if (defaultFilter === `${defaultFilter}Values`) {
                  currentFilter[defaultFilter] = filter(`${defaultFilter}Values`)
                } else {
                  currentFilter[defaultFilter] = filter(defaultFilter)
                }
            }

            this.currentFilter = currentFilter

            if (this.$route.query.user) {
                this.openUserModal(this.$route.query.user, 4)
            }
        },
        activated(){
            console.log('[Discover] Activated');

            if (this.isMobile) {
              this.resetUsersAround()
            }

            this.pullToRefreshInstance = this.attachPullToRefresh('#js-discover-content')

            if (app.isDesktop) {
                this.setScrollToDiscover();
            } else {
                if (this.$route.meta.back === '/search' || this.haveUnblockedUsers) {
                    // this.resetUsersAround();
                    this.$store.commit('setHaveUnblockedUsers', false)
                }
            }

            this.$nextTick(() => {
              setTimeout(() => {
                this.setScrollFromTag();
              }, 50);
            });
        },
        deactivated() {
            this.removeDisableScroll();
        },
        destroyed() {
            app.$off('reload-discover')
        }
    }
</script>
<style scoped>
  .discoverSearchTagsChild {
    width:100% !important;
  }
  .disable-scroll {
      overflow: hidden !important;
  }

  #application-wrapper {
    overflow: visible !important;
  }

  .content-wrapper {
    padding-top: 80px;
    overflow-x: hidden;
    overflow-y: scroll;
    position: absolute;
    top: 0;
    height: 100%;
    width: 100%;
  }

</style>

<style>
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
/*.mobilePopup .vm--modal {*/
/*  background: rgba(0, 0, 0, 0.4) !important;*/
/*  box-shadow: 10px 10px 10px rgb(0 0 0 / 50%) !important;*/
/*  width:90% !important;*/
/*  align-items: center;*/
/*  justify-content: center;*/
/*  display: flex;*/
/*}*/
.clearBothDiscoverSearch {
  clear: both;
  margin-bottom: 55px;
}

</style>