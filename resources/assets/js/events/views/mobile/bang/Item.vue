<template>
    <div class="w-views page-bang">
        <div class="content-wrapper">
            <div class="event-screen">
                <div class="event-details bang">
                    <div class="header">
                        <i class="back" @click="goBack()"></i>
                        <i class="dots" v-if="bang && bang.membership !== 'host'" v-show="!reportMenuVisible" v-on:click="showReportMenu"></i>
                    </div>

                    <BangDetails v-if="bang" :eventId="eventId"/>

                    <div class="mobileSidebarHolder positionRight"
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
                                                <input type="checkbox" name="wrong-type-event" value="wrong_category" v-model="reportData">
                                                <span class="checkbox-custom"></span>
                                                <div class="input-title">{{ trans('events.report.wrong_type') }}</div>
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
                                                <input type="checkbox" name="illegal-event" value="illegal" v-model="reportData">
                                                <span class="checkbox-custom"></span>
                                                <div class="input-title">{{ trans('events.report.illegal') }}</div>
                                            </label>
                                        </div>
                                        <div class="checkbox-container">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="spam-event" value="spam" v-model="reportData">
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
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters, mapState} from 'vuex';

    import BangDetails from "@events/views/widgets/bang/Item.vue";
    import eventsModule from '@events/module/store/type'
    import chatModule from '@chat/module/store/type'

    export default {
        components: {
            BangDetails
        },
        data() {
            return {
                menuVisible: false,
                reportMenuVisible: false,
                reportData: []
            }
        },
        props: ['eventId'],
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-events').default,
            require('@general/lib/mixin-bang').default,
        ],
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
            async chatBang() {
                if (!this.isMyEvent(this.bang)) {
                    this.goTo(`/chat/bang/${this.eventId}`);
                } else {
                    this.goTo('/chat');
                }
            },
        },
        computed: {
            ...mapState({
                blockedUsersIds: state => state.blockedUsersIds,
            }),
            ...mapGetters({
                unreadEventMessagesCount: chatModule.getters.messages.count.unreadEvent
            }),
            heartFilledIn() {
                return this.bang.isLiked
            },
            bang() {
                return this.$store.getters.getEvent(this.eventId)
            },
        },
        watch: {
            blockedUsersIds: function(blockedUsersIds) {
                if (blockedUsersIds.includes(this.bang?.user_id)) {
                    this.goTo('/events')
                }
            }
        },
        beforeMount() {
            this.$store.dispatch(eventsModule.actions.events.loadInfo, this.eventId)
        },
        activated() {
              console.log('[Event Mobile Item] Activated')
              this.$store.commit('updateUser', { has_event_notifications: false })
          }
    }
</script>
