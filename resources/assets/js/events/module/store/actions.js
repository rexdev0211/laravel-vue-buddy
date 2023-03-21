import qs from 'qs';
import Vue from "vue";

import eventsModule from "./type";
import chatModule from '@chat/module/store/type';
import mixin from '@general/lib/mixin';

import { _type as sidebarType } from '@general/modules/sidebar'
import newGroupMessageReceivedListener from '@chat/lib/listeners/newGroupMessageReceivedListener'
import updateGroupMessageListener from '@chat/lib/listeners/updateGroupMessageListener'

const actions = {
    async [eventsModule.actions.getStickyEvents] ({state, commit}) {
        try {
            let response = await axios.post('/api/stickyEvents')
            if (response.status === 200) {
                let data = response.data
                if (!data) {
                    return 0
                }

                commit(eventsModule.mutations.setStickyEvents, data)
            }
        } catch (error) {
            console.error(error)
        }
    },
    [eventsModule.actions.setBang]({commit}, payload) {
        if (payload.visible === false) {
            payload.mode = 'view'
            payload.eventId = null
        }
        commit(eventsModule.mutations.setBang, payload)
    },
    [eventsModule.actions.setEvent]({commit}, payload) {
        if (payload.visible === false) {
            payload.mode = 'view'
            payload.eventId = null
        }
        commit(eventsModule.mutations.setEvent, payload)
    },
    [eventsModule.actions.setType]({commit}, type) {
        commit(eventsModule.mutations.setType, type)
    },
    [eventsModule.actions.initFilter]({dispatch}) {
        let typeCached = localStorage.getItem('events-type')

        if (typeCached === 'friends') {
            typeCached = 'guide'
        }

        if (typeCached) {
            dispatch(eventsModule.actions.setType, typeCached)
        }
    },
    [eventsModule.actions.events.like]({store, commit}, event) {
        if (!event.isLiked) {
            axios.post(`/api/likeEvent`, {eventId: event.id})
                .then(() => {
                    event.isLiked = true;
                    commit(eventsModule.mutations.setLikes, event);
                })
        } else {
            axios.post(`/api/dislikeEvent`, {eventId: event.id})
                .then(() => {
                    event.isLiked = false;
                    commit(eventsModule.mutations.setLikes, event);
                })
        }
    },
    [eventsModule.actions.events.report](store, data) {
        axios.post('/api/reportEvent', data)
            .then(response => {
                data.callback(response)
            })
    },
    async [eventsModule.actions.events.load]({state, commit}) {
        let payload = {
            type: state.type
        }

        payload.page = state.page
        payload.limit = window.LOAD_EVENTS_DATES_PER_PAGE

        try {
            let response = await axios.post('/api/eventsAround', payload)
            if (response.status === 200) {
                let data = response.data
                if (!data) {
                    return 0
                }

                // Add dates
                commit(eventsModule.mutations.events.addDates, data)
                commit(eventsModule.mutations.events.setPageNum, { date: null, value: '+1'})

                return data.length
            }
        } catch (error) {
            console.error(error)
        }
        return 0
    },
    async [eventsModule.actions.events.loadMoreByDate]({state, commit, getters}, { date, index }) {
        let payload = {
            type: state.type
        }

        let existedDateIndex = state.eventDates.findIndex(v => v.date === date)
        if (existedDateIndex === -1)
            return

        let existedEntry = state.eventDates[existedDateIndex]
        if (!existedEntry.events_range_high_count) {
            return
        }

        payload.page = existedEntry.page || 0
        payload.date = date
        payload.limit = window.LOAD_EVENTS_PER_DATE_NEXT
        payload.except = getters[eventsModule.getters.eventIdsByDate](date)

        try {
            // Show preloader
            commit(eventsModule.mutations.setDateLoading, { date, value: true })

            let response = await axios.post('/api/eventsAround', payload)
            if (response.status === 200) {
                let data = response.data
                if (!data) {
                    return 0
                }

                // Hide preloader
                commit(eventsModule.mutations.setDateLoading, {date, value: false})

                // Add events to list
                commit(eventsModule.mutations.events.addDates, data)
                commit(eventsModule.mutations.events.setPageNum, { date, value: '+1'})
            }
        } catch (error) {
            console.error(error)
        }
    },
    async [eventsModule.actions.events.loadInfo](context, eventId) {
        try {
            let response = await axios.get(`/api/eventInfo/${eventId}`)
            if (response.status === 200) {
                Vue.set(context.rootState.eventsInfo, eventId, response.data)
                return response.data
            }
        } catch (error) {
            console.error(error)
        }
        return null
    },
    async [eventsModule.actions.events.submit]({ dispatch, commit }, data) {
        console.log('[Event Submit] Payload]', { data })

        let updatePerformed = data.id
        let params = []
        if (updatePerformed) {
            params = [
                `/api/updateEvent/${data.id}`,
                data,
                {'Content-Type': 'application/json'}
            ]
        } else {
            params = [
                '/api/createEvent',
                qs.stringify(data),
                {'Content-Type': 'application/x-www-form-urlencoded'}
            ]
        }
        let eventData = null
        return new Promise((resolve,reject) => {
            axios.post(...params).then((response) => {
                eventData = response.data.event
                console.log('[Submit Event] Response', { response })
                if (!!eventData.general.type) {
                    dispatch(eventsModule.actions.setType, eventData.general.type)
                }

                // Created
                if (!updatePerformed) {
                    mixin.methods.showSuccessNotification('event_successfully_added')
                    commit('addMyEvents', [eventData.general])

                    if (app.isDesktop) {
                        dispatch(eventsModule.actions.setBang, { visible: false })
                        dispatch(eventsModule.actions.setEvent, { visible: false })
                    } else {
                        dispatch(sidebarType.actions.hide)
                        app.goTo('/events')
                    }

                    // Listen to new event channel
                    if (eventData.general.type === 'bang') {
                        dispatch(eventsModule.actions.membership.addMy, eventData.general.id)
                    }

                    setTimeout(function(){
                        app.$emit('reload-events')
                    }, 750)

                    // Updated
                } else {
                    dispatch(eventsModule.actions.events.update, eventData)

                    if (app.isDesktop) {
                        dispatch(eventsModule.actions.setBang, { visible: false })
                        dispatch(eventsModule.actions.setEvent, { visible: false })

                        setTimeout(function(){
                            app.$emit('reload-events')
                        }, 750)
                    } else {
                        app.goTo(
                            eventData.general.type === 'bang' ?
                                `/bang/${eventData.general.id}`
                                :
                                `/event/${eventData.general.id}`
                        )
                    }
                }
                resolve('Ok')
            }).catch( (error) => {
                if(error.status === 422) {
                    mixin.methods.showErrorNotification(error.data.error)
                }
                reject('Error')
            });

        });
    },
    async [eventsModule.actions.events.remove]({ dispatch, commit }, eventId) {
        dispatch('showDialog', {
            mode: 'confirm',
            message: app.trans('sure_delete_event'),
            callback: async () => {
                console.log('[Delete Event] Callback')
                let response = await axios.post(`/api/removeEvent/${eventId}`)
                if (response.status === 200) {
                    dispatch(eventsModule.actions.events.cleanUp, {
                        eventId,
                        removeFromEventList: true
                    })
                }
            }
        })
    },
    [eventsModule.actions.events.cleanUp]({ dispatch, commit }, { eventId, removeFromEventList }) {
        if (removeFromEventList) {
            // Remove event from events list
            commit(eventsModule.mutations.events.remove, eventId)
        }

        // Remove event from myEvents list
        commit('removeFromMyEvents', eventId)
        // Remove conversation
        dispatch(chatModule.actions.conversations.remove, {eventId})
        // Remove my membership
        dispatch(eventsModule.actions.membership.removeMy, eventId)

        if (app.isDesktop) {
            // Close event modal
            dispatch(eventsModule.actions.setBang, { visible: false })
            dispatch(eventsModule.actions.setEvent, { visible: false })

            // Close event chat
            let eventModal = store.state.chatModule.modal
            if (
                eventModal.event
                &&
                eventModal.event.id == eventId
            ) {
                store.commit(chatModule.mutations.modal, {
                    event: null,
                    user: null,
                    mode: null,
                    minimized: false
                })
            }
        } else if (app.isMobile) {
            let routeEventId = app.$route.params && app.$route.params.eventId

            // Exit event chat
            if (['chat-group', 'chat-event-user'].includes(app.$route.name)) {
                app.goTo('/chat')

            // Exit event
            } else if (
                ['edit-event', 'edit-bang', 'bang', 'event'].includes(app.$route.name)
                &&
                routeEventId == eventId
            ) {
                app.goTo('/events')
            }
        }
    },
    [eventsModule.actions.resetPagination]({ commit }) {
        commit(eventsModule.mutations.resetPagination)
    },
    [eventsModule.actions.events.update]({ dispatch, commit, rootState }, { full, discover, general }) {
        if (general.id) {
            // My events
            commit('updateMyEvent', general)

            // Chat list
            dispatch(chatModule.actions.conversations.updateConversationEvent, {
                eventId: general.id,
                eventData: general
            })
        }

        if (discover.id) {
            // Events list
            commit(eventsModule.mutations.events.update, {
                eventId: discover.id,
                eventData: discover
            })
        }

        // Events cached info
        if (
            full.id
            &&
            rootState.eventsInfo[full.id]
        ) {
            Vue.set(
                rootState.eventsInfo,
                full.id,
                {...rootState.eventsInfo[full.id], ...full}
            )
        }
    },
    async [eventsModule.actions.membership.request](store, { eventId, userId, action}) {
        try {
            let response = await axios.post('/api/eventMembership', {
                eventId,
                userId,
                action
            })
            if (response.status === 200) {
                let eventData = response.data.event
                store.dispatch(eventsModule.actions.events.update, eventData)

                if (action === 'leave') {
                    // Clean up
                    store.dispatch(eventsModule.actions.events.cleanUp, {
                        eventId,
                        removeFromEventList: false
                    })
                }
            }
        } catch (error) {
            console.log(error)
        }
    },
    [eventsModule.actions.membership.set](store, payload) {
        payload.forEach(id => {
            store.dispatch(eventsModule.actions.membership.add, id)
        })
    },
    [eventsModule.actions.membership.setMy](store, payload) {
        payload.forEach(id => {
            store.dispatch(eventsModule.actions.membership.addMy, id)
        })
    },
    [eventsModule.actions.membership.add](store, eventId) {
        store.commit(eventsModule.mutations.membership.add, eventId)
    },
    [eventsModule.actions.membership.remove](store, eventId) {
        store.commit(eventsModule.mutations.membership.remove, eventId)
    },
    [eventsModule.actions.membership.addMy](store, eventId) {
        store.commit(eventsModule.mutations.membership.addMy, eventId)
        store.dispatch(eventsModule.actions.membership.subscribe, eventId)
    },
    [eventsModule.actions.membership.removeMy](store, eventId) {
        store.commit(eventsModule.mutations.membership.removeMy, eventId)
        store.dispatch(eventsModule.actions.membership.unsubscribe, eventId)
    },
    [eventsModule.actions.membership.subscribe](store, eventId) {
        if (window.Echo) {
            console.log('[Echo] Listening to channel-group-chat-' + eventId)
            window.Echo.private('channel-group-chat-' + eventId)
                .listen('NewGroupMessageReceived', newGroupMessageReceivedListener)
                .listen('UpdateGroupMessage', updateGroupMessageListener)
        } else {
            console.log('[Echo] No Echo. Cannot listen to channel-group-chat-' + eventId)
        }
    },
    [eventsModule.actions.membership.unsubscribe](store, eventId) {
        if (window.Echo) {
            console.log('[Echo] Leaving channel-group-chat-' + eventId)
            window.Echo.leave('channel-group-chat-' + eventId)
        } else {
            console.log('[Echo] No Echo. Cannot unsubscribe from channel-group-chat-' + eventId)
        }
    },
    [eventsModule.actions.membership.unsubscribeFromAll](store) {
        let memberships = store.state.myMemberships
        for (let eventId in memberships) {
            store.dispatch(eventsModule.actions.membership.unsubscribe, eventId)
        }
    }
}

export default actions
