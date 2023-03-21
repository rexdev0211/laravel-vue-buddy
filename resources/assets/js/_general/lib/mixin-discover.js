import _ from "lodash";
import discoverModule from "@discover/module/store/type";
import chatModule from '@chat/module/store/type';

import {mapGetters} from 'vuex';

export default {
    data: () => ({
        scrollTimeout: null
    }),
    methods: {
        elementInViewport(el, { viewportTop, viewportBottom }) {
            if (!el) {
                return false
            }

            let top = el.offsetTop
            let height = el.offsetHeight
            while (el.offsetParent) {
                el = el.offsetParent
                top += el.offsetTop
            }
            return (
                (top + height) >= viewportTop
                &&
                top <= viewportBottom
            )
        },
        handleDiscoverScroll: _.debounce(function({ scrollTop, offsetTop, offsetHeight }){
            // Abort "updateDiscoverUserStatus" action if user scrolled screen
            // It will be launched later - after user holds scroll for 1.5 sec
            if (this.scrollTimeout) {
                clearTimeout(this.scrollTimeout)
            }

            let self = this
            let visibleUsers = []
            let discoverContainer = $('#js-discover-content')
            let discoverContainerScrollTop = discoverContainer.scrollTop()
            let discoverContainerOffset = discoverContainer.offset() && discoverContainer.offset().top
            let discoverContainerHeight = discoverContainer.height()

            scrollTop = scrollTop || discoverContainerScrollTop || 0
            offsetTop = offsetTop || discoverContainerOffset || 0
            offsetHeight = offsetHeight || discoverContainerHeight || 300

            this.usersAround.forEach(function(user) {
                let el = document.getElementById('discover-user-' + user.id)
                let payload = {
                    viewportTop: scrollTop + offsetTop,
                    viewportBottom: scrollTop + offsetHeight
                }
                if (self.elementInViewport(el, payload)) {
                    visibleUsers.push({ id: user.id, loaded: user.loaded })
                }
            })

            this.$store.dispatch(discoverModule.actions.users.setVisible, visibleUsers)

            // Queue "updateDiscoverUserStatus" after scroll event.
            // If scroll event won't be fired withing 1.5 sec
            // then visible discover users will update
            // if there are any of them older than REFRESH_LAST_ACTIVE_SECONDS.
            this.scrollTimeout = setTimeout(() => {
                this.$store.dispatch('updateDiscoverUserStatus')
            }, 1500)
        }, 1000),
        async loadUsersAround(infiniteScroll) {
            let usersResponse,
                isSearchPage = this.$route.name === 'search';

            let requestData = {
                enableFilter: !isSearchPage && (!this.searchBuddyInput || !this.searchBuddyInput.length),
            }

            if (this.searchBuddyInput && this.searchBuddyInput.length) {
                requestData.filterName = this.searchBuddyInput;
            }

            usersResponse = await this.$store.dispatch(discoverModule.actions.users.load, requestData)

            console.log(`[Discover] ${usersResponse} users fetched (page=${this.page}/perPage=${window.LOAD_USERS_AROUND_LIMIT})`)

            if (usersResponse) {
                infiniteScroll.loaded();
                store.dispatch(discoverModule.actions.filter.setPage, this.page + 1)
            }

            if (usersResponse < window.LOAD_USERS_AROUND_LIMIT) {
                infiniteScroll.complete()
            }

            // Capture visible entries. No more, no less.
            this.handleDiscoverScroll({})
        },
        resetUsersAround(force=false){
            if (false === window.pageLoaded) {
                return;
            }

            console.log('[Discover] Reset')

            // Refresh gonna be performed only if it queued
            if (
                (this.$store.state.discoverModule.refreshQueued
                &&
                !this._inactive)
                ||
                force
            ) {
                store.dispatch(discoverModule.actions.users.setRefreshQueued, false)

                app.distance = window.PRE_SEARCH_USERS_AROUND_KM
                app.lastUsersAroundRefresh = moment()

                $('#js-discover-content').animate({scrollTop: 0}, 0, null)

                console.log('[Discover] Refresh queued. Reset users around list...', {
                    distance: app.distance,
                    lastUsersAroundRefresh: app.lastUsersAroundRefresh,
                })

                // Reset list
                this.$store.dispatch(discoverModule.actions.users.set, [])
                this.$store.dispatch(discoverModule.actions.filter.setPage, 0)


                // Reset infinite loading for desktop
                if (app.isDesktop) {
                    if (this.$parent.$refs.infiniteLoadingDiscover) {
                        this.$parent.$refs.infiniteLoadingDiscover.stateChanger.reset()
                    }
                }

                // Reset infinite loading
                if (this.$refs.infiniteLoadingDiscover){
                    this.$refs.infiniteLoadingDiscover.stateChanger.reset()
                }
            } else {
                console.log('[Discover] Refresh was not queued.')

                // Reload invalidated entries
                this.handleDiscoverScroll({})
            }
        }
    },
    computed: {
        ...mapGetters({
            discreetModeEnabled: 'discreetModeEnabled',
            unreadMessagesCount: chatModule.getters.messages.count.unread,
            enabledFiltersCount: discoverModule.getters.filter.countEnabled,
            usersAround: discoverModule.getters.usersAround,
            getFilterValue: discoverModule.getters.filter.get,
            usersNextPageCount: discoverModule.getters.usersNextPageCount,
            filterBuddies: discoverModule.getters.filterBuddies
        }),
        filterType(){
            return this.$store.getters[discoverModule.getters.filter.get]('filterType')
        },
        filterName(){
            return this.$store.getters[discoverModule.getters.filter.get]('filterName')
        },
        viewType(){
            return this.$store.getters[discoverModule.getters.filter.get]('viewType')
        },
        page(){
            return this.$store.getters[discoverModule.getters.filter.get]('page')
        },
        searchInput: {
            get(){
                return this.filterName
            },
            set: _.debounce(function(value){
                this.$store.dispatch(discoverModule.actions.filter.set, {
                    key: 'filterName',
                    value: value,
                    refresh: true
                })
            }, 500)
        }
    },
    created() {
        //console.log('[Discover] Created parent')
    },
    destroyed() {
        //console.log('[Discover] Destroyed parent')
    },
}
