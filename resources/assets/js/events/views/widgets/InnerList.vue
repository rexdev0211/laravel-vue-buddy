<template>
    <div>
        <div v-for="dataData in eventDates" v-if="dataData.events_range_high_count || (typeof dataData.events_range_low !== 'undefined' && dataData.events_range_low.length) || (typeof dataData.events_range_high !== 'undefined' && dataData.events_range_high.length)">
            <div>
                <div class="timeline" v-if="dataData.date === 'invited' || dataData.date === 'accepted invitations'">{{ trans('events.invited')  }}</div>
                <div class="timeline" v-else>{{ dataData.date | formatDate('day-date') }}</div>

                <!-- Low range -->
                <div class="events" v-for="event in dataData.events_range_low">
                    <EventListItem v-if="type !== 'bang'" :ev="event" :eventId="event.id"/>
                    <BangListItem v-else :bang="event" :invited="dataData.date === 'invited'" :accepted="dataData.date === 'accepted invitations'" />
                </div>

                <!-- High range -->
                <div class="events" v-for="event in dataData.events_range_high">
                    <BangListItem v-if="type === 'bang'" :bang="event" :invited="dataData.date === 'invited'" :accepted="dataData.date === 'accepted invitations'" />
                    <EventListItem v-else :ev="event" :eventId="event.id"/>
                </div>

                <div class="events" v-if="dataData.events_range_high_count">
                    <a v-if="!dataData.loading" class="btn load-more"
                        @click="loadMoreEventsByDate({ date: dataData.date })">
                        {{ trans('show_more') }}
                    </a>
                    <!-- Preloader -->
                    <div v-else class="b-event__preloader">
                        <img src="/assets/img/preloader.svg" alt="">
                    </div>
                </div>
            </div>
        </div>

        <infinite-loading
            ref="infiniteLoadingEvents"
            @infinite="loadEvents"
            :force-use-infinite-wrapper="getBlockIdForPagination"
            spinner="bubbles">
            <div class="event no-events" slot="no-results" v-if="showCreateYourEvent">
                <div class="inner">
                    <div class="title">{{ trans('no_events_around') }}</div>
                    <a id="add-event-link" @click="createEvent(type)">
                        Create your {{ type === 'bang' ? 'bang' : 'event' }}
                    </a>
                </div>
            </div>
            <div slot="no-results" v-else></div>
            <div class="event no-events" slot="no-more" v-if="showCreateYourEvent">
                <div class="inner">
                    <div class="title">{{ trans('no_more_event_around') }}</div>
                    <a id="add-event-link" @click="createEvent(type)">
                        Create your {{ type === 'bang' ? 'bang' : 'event' }}
                    </a>
                </div>
            </div>
            <div slot="no-more" v-else></div>
        </infinite-loading>

    </div>
</template>

<script>
    import {mapState, mapActions, mapGetters} from 'vuex';
    import EventListItem from '@events/views/widgets/event/ListItem.vue';
    import BangListItem from '@events/views/widgets/bang/ListItem.vue';
    import eventsModule from '@events/module/store/type'

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-events').default,
        ],
        data() {
            return {
                showCreateYourEvent: true
            }
        },
        computed: {
            ...mapState({
                myEvents: 'myEvents',
                userIsPro: 'userIsPro'
            }),
            ...mapGetters({
                type: eventsModule.getters.type,
                eventDates: eventsModule.getters.eventDates
            }),
            getBlockIdForPagination() {
                return app.isDesktop ? '#js-events-content' : '#application-wrapper'
            }
        },
        components: {
            EventListItem,
            BangListItem
        },
        methods: {
            ...mapActions({
                loadMoreEventsByDate: eventsModule.actions.events.loadMoreByDate,
            }),
            // fDate(value) {
            //   var isDate = function(value) {
            //     return (new Date(value) !== "Invalid Date") && !isNaN(new Date(value));
            //   }
            //
            //   if (!isDate) {
            //     return value;
            //   }
            //
            //   return value | formatDate('day-date');
            // },
        },
        watch: {
          eventDates: function (newVal) {
              console.log('Event Dates mounted', newVal);
              let eventCounter = 0;

              newVal.forEach(elem => {
                 eventCounter += elem.events_range_high_count;
              });
              this.showCreateYourEvent = !eventCounter >= 0;
          }
        },
        created() {
            console.log('[Events] Created')
            app.$on('reload-events', this.resetEventsAround)
        },
        destroyed() {
            console.log('[Events] Destroyed')
            app.$off('reload-events', this.resetEventsAround)
        },
        activated(){
            console.log('[Events] Activated')
            // this.$store.dispatch('showHealthAlert')
            this.$store.commit('updateUser', {
                has_event_notifications: false
            })
        }
    }
</script>
