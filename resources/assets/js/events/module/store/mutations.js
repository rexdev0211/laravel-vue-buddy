import eventsModule from "./type";
import { eventPhotosSort } from '@buddy/lib/helpers'

const mutations = {
    [eventsModule.mutations.setStickyEvents] (state, payload) {
        state.stickyEvents = payload
    },
    [eventsModule.mutations.setBang] (state, payload) {
        state.source.bang = {...state.source.bang, ...payload}
    },
    [eventsModule.mutations.setEvent] (state, payload) {
        state.source.event = {...state.source.event, ...payload}
    },
    [eventsModule.mutations.setType] (state, type) {
        state.type = type
        localStorage.setItem('events-type', type)
    },
    [eventsModule.mutations.resetPagination](state) {
        state.page = 0
    },
    [eventsModule.mutations.setDateLoading](state, { date, value }) {
        let existedDateIndex = state.eventDates.findIndex(v => v.date === date)
        if (existedDateIndex === -1) {
            return []
        }
        state.eventDates[existedDateIndex].loading = value
    },
    [eventsModule.mutations.events.setPageNum](state, { date, value }) {
        if (date) {
            let existedDateArr = state.eventDates.findIndex(v => v.date === date)
            if (existedDateArr !== -1) {
                if (value === '+1') {
                    state.eventDates[existedDateArr]['page'] = (state.eventDates[existedDateArr]['page'] || 0) + 1
                } else {
                    state.eventDates[existedDateArr]['page'] = value
                }
            }
        } else {
            if (value === '+1') {
                state.page++
            } else {
                state.page = value
            }
        }
    },
    [eventsModule.mutations.events.set](state, events) {
        state.eventDates = events
    },
    [eventsModule.mutations.events.update](state, { eventId, eventData }) {
        state.eventDates.forEach((dateEntry, dateIndex) => {
            dateEntry.events_range_low.forEach((event, index) => {
                if (event.id === eventId) {
                    Vue.set(
                        state.eventDates[dateIndex].events_range_low,
                        index,
                        {...event, ...eventData}
                    )
                }
            })
            dateEntry.events_range_high.forEach((event, index) => {
                if (event.id === eventId) {
                    Vue.set(
                        state.eventDates[dateIndex].events_range_high,
                        index,
                        {...event, ...eventData}
                    )
                }
            })
        })
    },
    [eventsModule.mutations.events.remove](state, eventId) {
        state.eventDates.forEach((dateEntry, dateIndex) => {
            dateEntry.events_range_low.forEach((event, index) => {
                if (event.id === eventId) {
                    Vue.delete(state.eventDates[dateIndex].events_range_low, index)
                }
            })
            dateEntry.events_range_high.forEach((event, index) => {
                if (event.id === eventId) {
                    Vue.delete(state.eventDates[dateIndex].events_range_high, index)
                }
            })
        })
    },
    [eventsModule.mutations.events.addDates](state, dates) {
        dates.forEach(newDateEntry => {
            let existedDateIndex = state.eventDates.findIndex(v => v.date === newDateEntry.date)
            if (existedDateIndex !== -1) {
                let existedEntry = _.cloneDeep(state.eventDates[existedDateIndex])
                if (newDateEntry.events_range_low.length) {
                    existedEntry.events_range_low.push(...newDateEntry.events_range_low)
                }

                if (newDateEntry.events_range_high.length) {
                    existedEntry.events_range_high.push(...newDateEntry.events_range_high)
                }

                existedEntry.events_range_high_count = newDateEntry.events_range_high_count

                Vue.set(state.eventDates, existedDateIndex, existedEntry)
            } else {
                newDateEntry.page = 0
                newDateEntry.loading = false
                state.eventDates.push(newDateEntry)
            }
        })
    },
    [eventsModule.mutations.membership.add](state, eventId) {
        Vue.set(state.membershipRequests, eventId, true)
    },
    [eventsModule.mutations.membership.remove](state, eventId) {
        Vue.delete(state.membershipRequests, eventId)
    },
    [eventsModule.mutations.membership.addMy](state, eventId) {
        Vue.set(state.myMemberships, eventId, true)
    },
    [eventsModule.mutations.membership.removeMy](state, eventId) {
        Vue.delete(state.myMemberships, eventId)
    },
    [eventsModule.mutations.setLikes](state, event) {
        let index,
            eventDates = state.eventDates;

        for (let eventIndex in eventDates) {
            let eventsRangeHigh = eventDates[eventIndex].events_range_high,
                eventsRangeLow = eventDates[eventIndex].events_range_low

            if (eventsRangeHigh.length > 0) {
                index = _.findIndex(eventsRangeHigh, (e) => {
                    return e.id === event.id;
                })

                if (index !== -1) {
                    if (event.isLiked) {
                        event.likes++
                    } else {
                        event.likes--
                    }

                    Vue.set(state.eventDates[eventIndex].events_range_high, index, event);
                }
            }

            if (eventsRangeLow.length > 0) {
                index = _.findIndex(eventsRangeLow, (e) => {
                    return e.id === event.id;
                })

                if (index !== -1) {
                    if (event.isLiked) {
                        event.likes++
                    } else {
                        event.likes--
                    }

                    Vue.set(state.eventDates[eventIndex].events_range_low, index, event);
                }
            }

            if (this.state.eventsInfo[event.id]) {
                this.state.eventsInfo[event.id] = event;
            }
        }
    }
}

export default mutations
