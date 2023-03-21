<template>
    <div class="event-screen">
        <vue-custom-scrollbar ref="customScrollbar" id="event-details" class="event-details bang" v-if="bang">
            <div class="header">
                <i class="back" @click="closeBang"></i>
                <i class="dots" v-if="bang.membership !== 'host'" v-show="!reportMenuVisible" v-on:click="showReportMenu"></i>
            </div>

            <BangDetails :eventId="eventId"/>

            <div class="mobileSidebarHolder positionRight forDesktop"
                v-show="showReportMenu"
                :class="{'active': reportMenuVisible}">
                <div class="mobileSidebarHide" @click="hideReportMenu"></div>
                <div class="mobileSidebar">

                    <div class="report-menu">
                        <div class="inner">
                            <div class="title">{{ trans('events.report.title') }}</div>
                            <div class="box">
                                <div class="checkbox-container">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="wrong_category" value="wrong_category" v-model="reportData">
                                        <span class="checkbox-custom"></span>
                                        <div class="input-title">{{ trans('events.report.wrong_type') }}</div>
                                    </label>
                                </div>
                                <div class="checkbox-container">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="under_age" value="under_age" v-model="reportData">
                                        <span class="checkbox-custom"></span>
                                        <div class="input-title">{{ trans('report_under_age') }}</div>
                                    </label>
                                </div>
                                <div class="checkbox-container">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="illegal" value="illegal" v-model="reportData">
                                        <span class="checkbox-custom"></span>
                                        <div class="input-title">{{ trans('events.report.illegal') }}</div>
                                    </label>
                                </div>
                                <div class="checkbox-container">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="spam" value="spam" v-model="reportData">
                                        <span class="checkbox-custom"></span>
                                        <div class="input-title">{{ trans('report_spam') }}</div>
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn" @click="sendEventReport">{{ trans('send') }}</button>
                        </div>
                    </div><!--report-menu-->

                </div>
            </div>
        </vue-custom-scrollbar>
    </div>
</template>

<script>
    import {mapActions, mapGetters, mapState} from 'vuex';

    import BangDetails from "@events/views/widgets/bang/Item.vue";
    import eventsModule from '@events/module/store/type';
    import chatModule from '@chat/module/store/type';

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-events').default,
            require('@general/lib/mixin-bang').default,
        ],
        props: ['eventId'],
        data() {
            return {
                menuVisible: false,
                reportMenuVisible: false,
                reportData: []
            }
        },
        components: {
            BangDetails,
            vueCustomScrollbar
        },
        computed: {
            ...mapState({
                blockedUsersIds: state => state.blockedUsersIds,
            }),
            bang() {
                return this.$store.getters.getEvent(this.eventId || this.bangData.eventId)
            },
            ...mapGetters({
                unreadEventMessagesCount: chatModule.getters.messages.count.unreadEvent
            }),
            heartFilledIn() {
                return this.bang.isLiked
            }
        },
        watch: {
            blockedUsersIds: function(blockedUsersIds) {
                if (blockedUsersIds.includes(this.bang?.user_id)) {
                    this.closeBang()
                }
            }
        },
        methods: {
            ...mapActions({
                setBang: eventsModule.actions.setBang,
                reportEvent: eventsModule.actions.events.report,
                toggleEventLike: eventsModule.actions.events.like
            }),
            showReportMenu() {
                this.reportMenuVisible = true;
            },
            hideReportMenu() {
                this.reportMenuVisible = false;
            },
            closeFilter() {
                this.hideReportMenu();
            },
            sendEventReport() {
              this.reportEvent({
                reason: this.reportData,
                id: this.eventId,
                callback: this.eventReportResponse,
              })
            },
            eventReportResponse(response) {
                if (response.data.success) {
                    this.showSuccessNotification(response.data.trans)
                } else {
                    this.showErrorNotification(response.data.trans)
                }
                this.closeFilter()
            },
        },
        mounted() {
            this.$store.dispatch(eventsModule.actions.events.loadInfo, this.eventId)
            this.$refs.customScrollbar.$forceUpdate()
        },
        activated() {
            console.log('[Event Desktop Item] Activated')
            this.$store.dispatch(eventsModule.actions.membership.remove, this.eventId)
            this.$store.commit('updateUser', { has_event_notifications: false })
        }
    }
</script>
