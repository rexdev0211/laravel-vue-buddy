import eventsModule from "./type";

const getters = {
    [eventsModule.getters.membershipRequests]: state => eventId => {
        return state.membershipRequests[eventId] || false
    },
    [eventsModule.getters.event]: state => {
        return state.source.event
    },
    [eventsModule.getters.bang]: state => {
        return state.source.bang
    },
    [eventsModule.getters.type]: state => {
        return state.type
    },
    [eventsModule.getters.eventDates]: state => {
        return state.eventDates
    },
    [eventsModule.getters.eventIdsByDate]: state => date => {
        let existedDateIndex = state.eventDates.findIndex(v => v.date === date)
        if (existedDateIndex === -1) {
            return []
        }
        let existedEntry = state.eventDates[existedDateIndex]
        let lowRangeEventIds = existedEntry.events_range_low.map(event => {
            return event.id
        })
        return lowRangeEventIds
    },
    [eventsModule.getters.stickyEvents]: state => {
        return state.stickyEvents[state.type]
    },
}

export default getters
