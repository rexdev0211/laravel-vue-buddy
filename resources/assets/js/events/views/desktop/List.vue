<template>
    <div>
        <div class="w-app">
            <div id="application-wrapper">

                <TopBarDesktop/>

                <div class="content-wrapper">
                    <div class="calendar-menu" v-if="myEvents.length" :class="{'opened': myEventsMenuVisible}">
                        <div class="minimise" @click="myEventsMenuVisible = !myEventsMenuVisible"></div>
                        <div class="inner">
                            <div class="box">
                                <div class="headline">{{ trans('events.my_events') }}</div>
                                <div class="events">
                                    <div class="event" v-for="event in myEvents" :data-event="event.membership"
                                        @click="openEvent(event.id, event.type)"
                                        :class="{'notificated': membershipRequests(event.id), 'new-event-entry': event.is_new || false}">
                                        <div class="img" :style="{'background': `url(${event.photo_small}) no-repeat center / cover`}">
                                          <div v-if="event.type === 'guide'"
                                               :class="{'pending': event.status === 'pending', 'rejected': event.status === 'declined'}"
                                          >
                                          </div>
                                        </div>
                                        <div class="details" v-if="event.type === 'bang'">
                                            <div class="status" v-if="event.membership === 'host'">{{ trans('host') }}</div>
                                            <div class="status" v-else-if="event.membership === 'member'">{{ trans('member') }}</div>
                                            <div class="status"  v-else-if="event.membership === 'requested'">{{ trans('pending') }}</div>
                                            <div class="location notranslate" v-if="event.locality"><span>{{ event.locality }}</span></div>
                                            <div class="date" v-if="event.date"><span>{{ event.date | formatDate('day-months-year') }}</span></div>
                                        </div>
                                        <div class="details" v-else-if="event.type === 'guide'">
                                            <div class="event-title">{{ event.title }}</div>
                                            <div class="location notranslate" v-if="event.locality"><span>{{ event.locality }}</span></div>
                                            <div class="date" v-if="event.date"><span>{{ event.date | formatDate('day-months-year') }}</span></div>
                                        </div>
                                        <div class="details" v-else>
                                            <div class="event-title">{{ event.title }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <vue-custom-scrollbar id="js-events-content" ref="vueCustomScrollbar" class="col calendar-list">
                        <div class="center">
                            <div class="tabs calendar-tabs">
                                <div :class="{'tab':true, 'active': type === typeItem}"
                                    v-for="typeItem in types"
                                    @click="setType(typeItem)">
                                    <span :class="{'notificated-bang':(typeItem === 'bang' && getInvitationsToBang.length && type !== typeItem)}">{{ trans(`events.type.${typeItem}`) }}</span>
                                </div>
                            </div>

                            <div class="tab-content-wrapper calendar-tabs" :class="{[type]: true}">
                                <div class="tab-content-inner">
                                    <div class="tab-content">
                                        <div class="events">

                                            <StickyEventsList :list="stickyEvents" :type="type"/>
                                            <InnerList/>

                                        </div>

                                        <div id="add-event" class="add-event-button"
                                            @click="createEvent('event')" :title="trans('events.new_event')">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </vue-custom-scrollbar>
                </div>
                <HideChats />
            </div>
        </div>
    </div>
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
    import InfiniteLoading from 'vue-infinite-loading';
    import {mapState, mapActions, mapGetters} from 'vuex';

    import TopBarDesktop from '@buddy/views/widgets/TopBarDesktop.vue';
    import StickyEventsList from '@events/views/widgets/StickyEventsList.vue';
    import InnerList from '@events/views/widgets/InnerList.vue';
    // import HealthAlert from '@buddy/views/widgets/HealthAlert.vue';

    import eventsModule from '@events/module/store/type'
    import HideChats from "@buddy/views/widgets/HideChats.vue";

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-events').default,
        ],
        data() {
            return {
                types: [
                    'guide',
                    'fun',
                    'bang'
                ],
                myEventsMenuVisible: false
            }
        },
        computed: {
            ...mapState({
                myEvents: 'myEvents',
                userIsPro: 'userIsPro',
            }),
            ...mapGetters({
                type: eventsModule.getters.type,
                stickyEvents: eventsModule.getters.stickyEvents,
                eventDates: eventsModule.getters.eventDates,
                getInvitationsToBang:'getInvitationsToBang',
            }),
        },
        components: {
            InfiniteLoading,
            TopBarDesktop,
            InnerList,
            StickyEventsList,
            HideChats,
            vueCustomScrollbar
            // HealthAlert
        },
        methods: {
            ...mapActions({
                requirementsAlertShow: 'requirementsAlertShow',
                setBang: eventsModule.actions.setBang,
                setEvent: eventsModule.actions.setEvent,
                getStickyEvents: eventsModule.actions.getStickyEvents,
            }),
            setType(type) {
                console.log('[Events List] setType')
                this.$store.dispatch(eventsModule.actions.setType, type)

                this.$store.dispatch(eventsModule.actions.setBang, { visible: false });
                this.$store.dispatch(eventsModule.actions.setEvent, { visible: false });

                app.$emit('reload-events')
                // this.$store.dispatch('showHealthAlert')
            },
            createEvent(type) {
                if (!this.userIsPro && this.myEvents.length >= window.FREE_EVENTS_LIMIT) {
                    this.requirementsAlertShow('events')
                } else {
                    let payload = { visible: true, mode: 'create' }
                    if (type === 'bang') {
                        this.setBang(payload)
                    } else {
                        this.setEvent(payload)
                    }
                }
            }
        },
        mounted() {
            console.log('[Events] Mounted')
            let eventId = this.$route.query.eventId
            let bangId = this.$route.query.bangId
            if (eventId) {
                this.openEvent(eventId, 'event')
            }
            if (bangId) {
                this.openEvent(bangId, 'bang')
            }
            this.getStickyEvents()

            this.loadScrollTopButton('js-events-content');
        },
        deactivated() {
            console.log('[Events] Destroyed')
            this.$store.dispatch(eventsModule.actions.setBang, { visible: false });
            this.$store.dispatch(eventsModule.actions.setEvent, { visible: false });
            app.$off('reload-events', this.resetEventsAround)
        },
        watch: {
          eventDates() {
              this.$refs.vueCustomScrollbar.$forceUpdate()
          }
        }
    }
</script>
