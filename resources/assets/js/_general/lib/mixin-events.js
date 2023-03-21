import {mapGetters, mapActions} from 'vuex';

import eventsModule from '@events/module/store/type'

export default {
    computed: {
        ...mapGetters({
            membershipRequests: eventsModule.getters.membershipRequests
        })
    },
    methods: {
        ...mapActions({
            requirementsAlertShow: 'requirementsAlertShow',
        }),
        createEvent(type) {
            if (!this.userIsPro && this.myEvents.length >= window.FREE_EVENTS_LIMIT) {
                this.requirementsAlertShow('events')
            } else {
                let entityType = type === 'bang' ? 'event' : 'event'
                console.log('createEvent', { entityType })
                if (this.isDesktop) {
                    this.openEventModal(null, entityType, 'create')
                } else {
                    this.$router.push({ name: 'create-' + entityType })
                }
            }
        },
        editEvent(eventId, type){
            let entityType = type === 'bang' ? 'bang' : 'event'
            console.log('editEvent', { entityType, eventId })
            if (this.isDesktop) {
                this.openEventModal(eventId, entityType, 'edit')
            } else {
                this.$router.push({
                    name: 'edit-' + entityType,
                    params: { eventId }
                })
            }
        },
        openEvent(eventId, type) {
            let entityType = type === 'bang' ? 'bang' : 'event'
            console.log('openEvent', { entityType, eventId })
            if (this.isDesktop) {
                if (this.$route.name !== 'events') {
                    this.$router.push({name:'events'})
                }
                this.openEventModal(eventId, entityType)
            } else {
                this.$router.push({
                    name: entityType,
                    params: { eventId }
                })
            }
            this.$store.commit('updateMyEvent', {
                id: eventId,
                is_new: false
            })
        },
        async openEventModal(eventId, type, mode) {
            this.$store.dispatch(eventsModule.actions.setBang, { visible: false });
            this.$store.dispatch(eventsModule.actions.setEvent, { visible: false });
            if (mode !== 'create') {
                await this.$store.dispatch(eventsModule.actions.events.loadInfo, eventId)
            }

            let payload = {
                visible: true,
                eventId: eventId,
                mode: mode || 'view'
            }
            console.log('[openEventModal]', payload)

            this.$store.dispatch(
                type === 'bang' ?
                    eventsModule.actions.setBang
                    :
                    eventsModule.actions.setEvent,
                payload
            )
        },
        closeEvent() {
            this.$store.dispatch(eventsModule.actions.setEvent, { visible: false })
        },
        closeBang() {
            this.$store.dispatch(eventsModule.actions.setBang, { visible: false })
        },
        async handleEventInvitation(type, data)
        {
            return await axios.post('/api/handle-invitation/'+type, data);
        },
        acceptEventInvitation(data)
        {
            return this.handleEventInvitation('accept', data);
        },
        declineEventInvitation(data)
        {
            return this.handleEventInvitation('decline', data);
        },
        resetEventsAround() {
            console.log('[Events] Reset')

            this.$store.commit(eventsModule.mutations.events.set, []);
            this.$store.commit(eventsModule.mutations.resetPagination)

            app.lastEventsAroundRefresh = moment();

            //otherwise it would scroll to the bottom when you change filter
            $('#js-events-content').animate({scrollTop: 0}, 0);

            if (this.$refs.infiniteLoadingEvents) {
                this.$refs.infiniteLoadingEvents.stateChanger.reset()
            } else {
                console.log('No infiniteLoadingEvents ref found')
            }
        },
        async loadEvents(infiniteScroll) {
            let datesCount = await this.$store.dispatch(eventsModule.actions.events.load)
            if (datesCount) {
                infiniteScroll.loaded()
            }
            if (datesCount < window.LOAD_EVENTS_DATES_PER_PAGE) {
                infiniteScroll.complete()
            }
        },
    }
}
