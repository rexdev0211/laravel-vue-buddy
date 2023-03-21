<template>
    <div class="w-root">
        <div class="w-views">
            <div id="application-wrapper">
                <div class="content-wrapper">
                    <div class="conversation-screen">
                        <div class="conversation user" v-if="user">

                            <div class="header">
                                <i class="back" @click="goTo('/chat')"></i>

                                <div class="info user" @click="goToUser"
                                    :class="{'online': user.isOnline, 'was-online': user.wasRecentlyOnline && !user.isOnline}">
                                    <div class="img" 
                                        :class="{'is-deleted': !!user.deleted_at}"
                                        :style="!user.deleted_at && {'background': `url(${user.photo_small}) no-repeat center / cover`}">
                                    </div>
                                    <div class="col">
                                        <div class="name notranslate">{{ user.name }}</div>
                                        <div class="how-far notranslate">{{ getDistanceString(user) }}</div>
                                    </div>
                                </div>

                                <i class="dots" @click="menuVisible = !menuVisible"></i>
                                <div class="block-report" :class="{'open': menuVisible}">
                                    <div id="block-profile" class="option block"
                                        v-show="!reportMenuVisible" v-on:click="blockUser">
                                        <span>{{ trans('block_user') }}</span>
                                    </div>
                                    <div id="report-profile" class="option report"
                                        v-show="!reportMenuVisible" v-on:click="showReportMenu">
                                        <span>{{ trans('report_user') }}</span>
                                    </div>
                                </div>
                            </div>

                            <vue2-gesture :type="'swipeRight'" :call="handleGesture.bind(this, 'swipeRight')">
                                <ChatComponent chatMode="user" :userId="parseInt(userId)" v-if="user"></ChatComponent>
                            </vue2-gesture>
                        </div>
                    </div>

                    <div class="mobileSidebarHolder positionRight"
                         v-show="showReportMenu"
                         :class="{'active': reportMenuVisible}">
                        <div class="mobileSidebarHide" @click="hideReportMenu"></div>
                        <div class="mobileSidebar">

                            <div class="report-menu">
                                <div class="inner">
                                    <div class="title">{{ trans('report_user') }}</div>
                                    <div class="box">
                                        <div class="checkbox-container">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="spam" value="spam" v-model="reportData">
                                                <span class="checkbox-custom"></span>
                                                <div class="input-title">{{ trans('report_spam') }}</div>
                                            </label>
                                        </div>
                                        <div class="checkbox-container">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="fake" value="fake" v-model="reportData">
                                                <span class="checkbox-custom"></span>
                                                <div class="input-title">{{ trans('report_fake') }}</div>
                                            </label>
                                        </div>
                                        <div class="checkbox-container">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="harassment" value="harassment" v-model="reportData">
                                                <span class="checkbox-custom"></span>
                                                <div class="input-title">{{ trans('report_harassment') }}</div>
                                            </label>
                                        </div>
                                        <div class="checkbox-container">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="under-age" value="under_age" v-model="reportData">
                                                <span class="checkbox-custom"></span>
                                                <div class="input-title">{{ trans('report_under_age') }}</div>
                                            </label>
                                        </div>
                                        <div class="checkbox-container">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="other" value="other" v-model="reportData">
                                                <span class="checkbox-custom"></span>
                                                <div class="input-title">{{ trans('report_other') }}</div>
                                            </label>
                                        </div>
                                    </div>
                                    <button type="send" class="btn" @click="reportUser">{{ trans('send') }}</button>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!--content-wrapper-->
            </div><!--#application-wrapper-->
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import { mapState } from 'vuex'
    import ChatComponent from '@chat/views/widgets/ChatComponent.vue';
    import chatModule from '@chat/module/store/type'

    export default {
        props: ['userToken'],
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-chat').default,
        ],
        data() {
            return {
                menuVisible: false,
                reportMenuVisible: false,
                reportData: []
            }
        },
        computed: {
            ...mapState({
                blockedUsersIds: state => state.blockedUsersIds,
            }),
            user(){
                return this.$store.getters.getUser(this.userToken)
            },
            userId(){
                return this.user ? this.user.id : null
            }
        },
        watch: {
            blockedUsersIds: function(blockedUsersIds) {
                if (blockedUsersIds.includes(this.user?.id)) {
                    this.goTo('/chat')
                }
            }
        },
        components: {
            ChatComponent
        },
        methods: {
            goToUser() {
                if (this.user.deleted_at) {
                    this.showErrorNotification('profile_is_deleted');
                } else if (this.user.status === 'deactivated') {
                    this.showErrorNotification('profile_is_deactivated');
                } else {
                    let userToken = this.user.link || this.user.id
                    this.goToOrBack('/user/' + userToken);
                    //this.openUserMobileModal(userToken);
                }
            },
            chatUser() {
                let userToken = this.user.link || this.user.id
                this.goToOrBack('/chat/' + userToken);
            },
            async blockUser() {
              if (!this.$store.state.userIsPro && this.$store.state.blockedCount >= window.FREE_BLOCKS_LIMIT) {
                await this.$store.dispatch('requirementsAlertShow', 'blocks')
              } else {
                await this.blockUserById(this.userId)
                this.goTo('/discover')
              }
            },
            showReportMenu() {
                this.reportMenuVisible = true;
            },
            hideReportMenu() {
                this.reportMenuVisible = false;
            },
            hideMenu() {
                this.hideReportMenu()
                this.menuVisible = false
            },
            reportUser() {
                app.showLightLoading(true);
                axios.post(`/api/reportUser/${this.userId}?type=${this.reportData}`)
                    .then(() => {
                        app.showLightLoading(false);
                        this.showSuccessNotification('user_reported_confirmation');
                        this.closeFilter();
                    });
            },
            closeFilter() {
                this.hideReportMenu();
            },
            handleGesture(str, e) {
                if (str === 'swipeRight') {
                    // Is there something suppressed swipe?
                    if (this.$store.state.chatModule.swipeEnabled) {
                        this.goTo('/chat')
                    // If it is than unblock swipes
                    } else {
                        this.$store.dispatch(chatModule.actions.toggleSwipe, true)
                    }
                }
            },
        },
        mounted(){
            if (!!this.userToken && !this.user){
                this.$store.dispatch('loadUserInfo', this.userToken)
            }
        }
    }
</script>
