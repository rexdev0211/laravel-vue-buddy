import notificationsModule from '@notifications/module/store/type';

export default {
    data: () =>({
        refreshQueued: false,
        scroll: 0,
        refresh: null,
        show: true,
    }),
    computed: {
        waveIcon() {
            return this.getWaveTapIcon(this.myTapForUser.sub_type);
        },
        myTapForUser() {
            let index = this.$store.state.myTaps.findIndex(el => { return el.user_to == this.userId && moment(el.idate).isAfter(moment().subtract(24, 'hour')) });
            if (index > -1) {
                return this.$store.state.myTaps[index];
            }
            return false;
        },
        waveIsAllowed() {
            return !this.isSelfUser && this.myTapForUser === false;
        },
        isSelfUser() {
            return this.userId == auth.getUserId();
        },
    },
    methods: {
        async getVisitors(infiniteScroll) {
            console.log('[getVisitors]', {infiniteScroll})
            const loadVisitorsLimit = window.LOAD_VISITORS_LIMIT
            let itemsCount = await this.$store.dispatch(notificationsModule.actions.visitors.load)
            if (itemsCount) {
                infiniteScroll.loaded()
            }
            if (itemsCount < loadVisitorsLimit) {
                infiniteScroll.complete()
            }
        },
        async getVisited(infiniteScroll) {
            console.log('[getVisited]', {infiniteScroll})
            const loadVisitedLimit = window.LOAD_VISITORS_LIMIT
            let itemsCount = await this.$store.dispatch(notificationsModule.actions.visited.load)
            if (itemsCount) {
                infiniteScroll.loaded()
            }
            if (itemsCount < loadVisitedLimit) {
                infiniteScroll.complete()
            }
        },
        async getNotifications(infiniteScroll) {
            console.log('[getNotifications]', {infiniteScroll})
            const loadNotificationsLimit = window.LOAD_TAPS_LIMIT;
            let itemsCount = await this.$store.dispatch(notificationsModule.actions.notifications.load)
            if (itemsCount) {
                infiniteScroll.loaded()
            }
            if (itemsCount < loadNotificationsLimit) {
                infiniteScroll.complete()
            }
        },
        toggleTapVisible() {
            if (this.waveIsAllowed) {
                this.tapsVisible = !this.tapsVisible;
            }
        },
        hideTaps() {
            this.tapsVisible = false;
        },
		waveToUser(recipientId, type) {
			let self = this
			this.$store.dispatch(
				notificationsModule.actions.notifications.addWave, 
				{ 
					recipientId, 
					type, 
					callback: function(res){
						if (typeof(res.data) == 'object') {
							self.$store.commit('addMyTap', res.data)
						}
					}
				}
			)
		},
        softReload(source){
            console.log('[softReload]', {
                source,
                active: !this._inactive,
                refreshQueued: this.refreshQueued
            })

            if (this.refreshQueued) {
                console.log(`${source} is ACTIVE, reloading`)
                this.reload()
                this.refreshQueued = false
            }
        },
        invalidate(){
            this.refreshQueued = true
        },
        checkScroll(target) {
            if (this.$refs.mobileScrollTopContainer) {
                let currentScroll = this.$refs.mobileScrollTopContainer.scrollTop

                if (currentScroll > this.scroll && currentScroll > 50 && this.show) {
                    this.show = false
                } else if (currentScroll < this.scroll && !this.show || currentScroll <= 50) {
                    this.show = true
                }

                this.scroll = currentScroll

                if (this.$refs.mobileScrollTopContainer.scrollTop > 5) {
                    if (this.refresh)
                        this.refresh.destroy()
                } else {
                    this.refresh = this.attachPullToRefresh(target)
                }
            }
        },
        pullRefresh(){
            this.reload()
        },
        initPullToRefresh(target){
            this.refresh = this.attachPullToRefresh(target)
            let self = this
            if (app.isMobile && this.$refs && this.$refs.mobileScrollTopContainer) {
                this.$refs.mobileScrollTopContainer.addEventListener('scroll', function() {
                    self.checkScroll(target)
                })
            }
        }
    },
    watch:{
        refreshQueued(value){
            console.log('[refreshQueued watcher]', {value})
            if (value && !this._inactive) {
                this.reload()
                this.refreshQueued = false
            }
        }
    }
}