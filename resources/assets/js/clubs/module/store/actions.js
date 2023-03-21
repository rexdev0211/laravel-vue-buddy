import qs from 'qs';
import Vue from "vue";

import clubsModule from "./type";
import chatModule from '@chat/module/store/type';
import mixin from '@general/lib/mixin';

import { _type as sidebarType } from '@general/modules/sidebar'
import newGroupMessageReceivedListener from '@chat/lib/listeners/newGroupMessageReceivedListener'
import updateGroupMessageListener from '@chat/lib/listeners/updateGroupMessageListener'

const actions = {
    async [clubsModule.actions.getStickyClubs] ({state, commit}) {
        try {
            let response = await axios.post('/api/stickyClubs')
            if (response.status === 200) {
                let data = response.data
                if (!data) {
                    return 0
                }

                commit(clubsModule.mutations.setStickyClubs, data)
            }
        } catch (error) {
            console.error(error)
        }
    },
    [clubsModule.actions.setClub]({commit}, payload) {
        if (payload.visible === false) {
            payload.mode = 'view'
            payload.clubId = null
        }
        commit(clubsModule.mutations.setClub, payload)
    },
    [clubsModule.actions.setType]({commit}, type) {
        commit(clubsModule.mutations.setType, type)
    },
    [clubsModule.actions.initFilter]({dispatch}) {
        let typeCached = localStorage.getItem('clubs-type')

        if (typeCached === 'friends') {
            typeCached = 'guide'
        }

        if (typeCached) {
            dispatch(clubsModule.actions.setType, typeCached)
        }
    },
    [clubsModule.actions.clubs.like]({store, commit}, club) {
        if (!club.isLiked) {
            axios.post(`/api/likeClub`, {clubId: club.id})
                .then(() => {
                    club.isLiked = true;
                    commit(clubsModule.mutations.setLikes, club);
                })
        } else {
            axios.post(`/api/dislikeClub`, {clubId: club.id})
                .then(() => {
                    club.isLiked = false;
                    commit(clubsModule.mutations.setLikes, club);
                })
        }
    },
    [clubsModule.actions.clubs.report](store, data) {
        axios.post('/api/reportClub', data)
            .then(response => {
                data.callback(response)
            })
    },
    async [clubsModule.actions.clubs.load]({state, commit}) {
        console.log('clubsModule.actions.clubs.load')
        let payload = {
            type: state.type
        }

        payload.page = state.page
        payload.limit = window.LOAD_CLUBS_PER_PAGE

        try {
            let response = await axios.post('/api/clubsAround', payload)
            if (response.status === 200) {
                let data = response.data
                if (!data) {
                    return 0
                }

                if (state.type === 'discover') {
                    commit(clubsModule.mutations.clubs.add, data)
                    commit(clubsModule.mutations.clubs.setPageNum, { value: '+1'})
                } else {
                    commit(clubsModule.mutations.clubs.set, data)
                }

                return state.type === 'discover' ? data.clubs_nearby.length + data.clubs_more.length : 0;
            }
        } catch (error) {
            console.error(error)
        }
        return 0
    },
    async [clubsModule.actions.clubs.loadMoreClubs]({state, commit}) {
        console.log('clubsModule.actions.clubs.loadMoreClubs')
        let payload = {
            type: state.type
        }

        // let existedDateIndex = state.clubDates.findIndex(v => v.date === date)
        // if (existedDateIndex === -1)
        //     return

        // let existedEntry = state.clubDates[existedDateIndex]
        // if (!existedEntry.clubs_range_high_count) {
        //     return
        // }

        // let payload = {};
        payload.page = state.page || 0
        payload.limit = window.LOAD_CLUBS_PER_PAGE

        try {
            // Show preloader
            // commit(clubsModule.mutations.setDateLoading, { date, value: true })

            let response = await axios.post('/api/clubsAround', payload)
            if (response.status === 200) {
                let data = response.data
                if (!data) {
                    return 0
                }

                // Hide preloader
                // commit(clubsModule.mutations.setDateLoading, {date, value: false})

                // Add clubs to list
                commit(clubsModule.mutations.clubs.add, data)
                commit(clubsModule.mutations.clubs.setPageNum, { value: '+1'})
            }
        } catch (error) {
            console.error(error)
        }
    },
    async [clubsModule.actions.clubs.loadMoreByDate]({state, commit, getters}, { date, index }) {
        let payload = {
            type: state.type
        }

        let existedDateIndex = state.clubDates.findIndex(v => v.date === date)
        if (existedDateIndex === -1)
            return

        let existedEntry = state.clubDates[existedDateIndex]
        if (!existedEntry.clubs_range_high_count) {
            return
        }

        payload.page = existedEntry.page || 0
        payload.date = date
        payload.limit = window.LOAD_EVENTS_PER_DATE_NEXT
        payload.except = getters[clubsModule.getters.clubIdsByDate](date)

        try {
            // Show preloader
            commit(clubsModule.mutations.setDateLoading, { date, value: true })

            let response = await axios.post('/api/clubsAround', payload)
            if (response.status === 200) {
                let data = response.data
                if (!data) {
                    return 0
                }

                // Hide preloader
                commit(clubsModule.mutations.setDateLoading, {date, value: false})

                // Add clubs to list
                commit(clubsModule.mutations.clubs.addDates, data)
                commit(clubsModule.mutations.clubs.setPageNum, { date, value: '+1'})
            }
        } catch (error) {
            console.error(error)
        }
    },
    async [clubsModule.actions.clubs.loadInfo](context, clubId) {
        try {
            let response = await axios.get(`/api/clubInfo/${clubId}`)
            if (response.status === 200) {
                Vue.set(context.rootState.clubsInfo, clubId, response.data)
                return response.data
            }
        } catch (error) {
            console.error(error)
        }
        return null
    },
    async [clubsModule.actions.clubs.submit]({ dispatch, commit }, data) {
        console.log('[Club Submit] Payload]', { data })

        let updatePerformed = data.id
        let params = []
        if (updatePerformed) {
            params = [
                `/api/updateClub/${data.id}`,
                data,
                {'Content-Type': 'application/json'}
            ]
        } else {
            params = [
                '/api/createClub',
                qs.stringify(data),
                {'Content-Type': 'application/x-www-form-urlencoded'}
            ]
        }
        let clubData = null
        return new Promise((resolve,reject) => {
            axios.post(...params).then((response) => {
                clubData = response.data.club
                console.log('[Submit Club] Response', { response })
                if (!!clubData.general.type) {
                    dispatch(clubsModule.actions.setType, 'my_clubs')
                }

                // Created
                if (!updatePerformed) {
                    mixin.methods.showSuccessNotification('club_successfully_added')
                    // commit('addMyClubs', [clubData.general])

                    if (app.isDesktop) {
                        dispatch(clubsModule.actions.setClub, { visible: false })
                    } else {
                        dispatch(sidebarType.actions.hide)
                        app.goTo('/clubs')
                    }

                    // Listen to new club channel
                    dispatch(clubsModule.actions.membership.addMy, clubData.general.id)

                    setTimeout(function(){
                        app.$emit('reload-clubs')
                    }, 750)

                    // Updated
                } else {
                    dispatch(clubsModule.actions.clubs.update, clubData)

                    if (app.isDesktop) {
                        dispatch(clubsModule.actions.setClub, { visible: false })

                        setTimeout(function(){
                            app.$emit('reload-clubs')
                        }, 750)
                    } else {
                        app.goTo(`/club/${clubData.general.id}`)
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
    async [clubsModule.actions.clubs.remove]({ dispatch, commit }, clubId) {
        dispatch('showDialog', {
            mode: 'confirm',
            message: app.trans('sure_delete_club'),
            callback: async () => {
                console.log('[Delete Club] Callback')
                let response = await axios.post(`/api/removeClub/${clubId}`)
                if (response.status === 200) {
                    dispatch(clubsModule.actions.clubs.cleanUp, {
                        eventId: clubId,
                        removeFromClubList: true
                    })
                }
            }
        })
    },
    [clubsModule.actions.clubs.cleanUp]({ dispatch, commit }, { eventId, removeFromClubList }) {
        if (removeFromClubList) {
            // Remove club from clubs list
            commit(clubsModule.mutations.clubs.remove, eventId)
        }

        // Remove club from myClubs list
        commit('removeFromMyClubs', eventId)
        // Remove conversation
        dispatch(chatModule.actions.conversations.remove, {eventId})
        // Remove my membership
        dispatch(clubsModule.actions.membership.removeMy, eventId)

        if (app.isDesktop) {
            // Close club modal
            // dispatch(clubsModule.actions.setBang, { visible: false })
            dispatch(clubsModule.actions.setClub, { visible: false })

            // Close club chat
            let clubModal = store.state.chatModule.modal
            if (
                clubModal.club
                &&
                clubModal.club.id == eventId
            ) {
                store.commit(chatModule.mutations.modal, {
                    club: null,
                    user: null,
                    mode: null,
                    minimized: false
                })
            }
        } else if (app.isMobile) {
            let routeClubId = app.$route.params && app.$route.params.clubId

            // Exit club chat
            if (['chat-group', 'chat-club-user'].includes(app.$route.name)) {
                app.goTo('/chat')

            // Exit club
            } else if (
                ['edit-club', 'edit-bang', 'bang', 'club'].includes(app.$route.name)
                &&
                routeClubId == eventId
            ) {
                app.goTo('/clubs')
            }
        }
    },
    [clubsModule.actions.resetPagination]({ commit }) {
        commit(clubsModule.mutations.resetPagination)
    },
    [clubsModule.actions.clubs.update]({ dispatch, commit, rootState }, { full, discover, general }) {
        if (general.id) {
            // My clubs
            commit('updateMyClub', general)

            // Chat list
            dispatch(chatModule.actions.conversations.updateConversationEvent, {
                eventId: general.id,
                eventData: general
            })
        }

        if (discover.id) {
            // Clubs list
            commit(clubsModule.mutations.clubs.update, {
                clubId: discover.id,
                clubData: discover
            })
        }

        // Clubs cached info
        if (
            full.id
            &&
            rootState.clubsInfo[full.id]
        ) {
            Vue.set(
                rootState.clubsInfo,
                full.id,
                {...rootState.clubsInfo[full.id], ...full}
            )
        }
    },
    async [clubsModule.actions.membership.request](store, { eventId, userId, action}) {
        try {
            let response = await axios.post('/api/clubMembership', {
                eventId,
                userId,
                action
            })
            if (response.status === 200) {
                let clubData = response.data.club
                store.dispatch(clubsModule.actions.clubs.update, clubData)

                if (action === 'leave') {
                    // Clean up
                    store.dispatch(clubsModule.actions.clubs.cleanUp, {
                        eventId,
                        removeFromClubList: false
                    })
                }
            }
        } catch (error) {
            console.log(error)
        }
    },
    async [clubsModule.actions.membership.requestAll](store, { eventId, action}) {
        console.log('[clubsModule.actions.membership.requestAll]', eventId, ' ', action)
        try {
            let response = await axios.post('/api/clubMembership', {
                eventId,
                action
            })
            if (response.status === 200) {
                let clubData = response.data.club
                store.dispatch(clubsModule.actions.clubs.update, clubData)

                if (action === 'leave') {
                    // Clean up
                    store.dispatch(clubsModule.actions.clubs.cleanUp, {
                        eventId,
                        removeFromClubList: false
                    })
                }
            }
        } catch (error) {
            console.log(error)
        }
    },
    [clubsModule.actions.membership.set](store, payload) {
        payload.forEach(id => {
            store.dispatch(clubsModule.actions.membership.add, id)
        })
    },
    [clubsModule.actions.membership.setMy](store, payload) {
        payload.forEach(id => {
            store.dispatch(clubsModule.actions.membership.addMy, id)
        })
    },
    [clubsModule.actions.membership.add](store, clubId) {
        store.commit(clubsModule.mutations.membership.add, clubId)
    },
    [clubsModule.actions.membership.remove](store, clubId) {
        store.commit(clubsModule.mutations.membership.remove, clubId)
    },
    [clubsModule.actions.membership.addMy](store, clubId) {
        store.commit(clubsModule.mutations.membership.addMy, clubId)
        store.dispatch(clubsModule.actions.membership.subscribe, clubId)
    },
    [clubsModule.actions.membership.removeMy](store, clubId) {
        store.commit(clubsModule.mutations.membership.removeMy, clubId)
        store.dispatch(clubsModule.actions.membership.unsubscribe, clubId)
    },
    [clubsModule.actions.membership.subscribe](store, clubId) {
        if (window.Echo) {
            console.log('[Echo] Listening to channel-group-chat-' + clubId)
            window.Echo.private('channel-group-chat-' + clubId)
                .listen('NewGroupMessageReceived', newGroupMessageReceivedListener)
                .listen('UpdateGroupMessage', updateGroupMessageListener)
        } else {
            console.log('[Echo] No Echo. Cannot listen to channel-group-chat-' + clubId)
        }
    },
    [clubsModule.actions.membership.unsubscribe](store, clubId) {
        if (window.Echo) {
            console.log('[Echo] Leaving channel-group-chat-' + clubId)
            window.Echo.leave('channel-group-chat-' + clubId)
        } else {
            console.log('[Echo] No Echo. Cannot unsubscribe from channel-group-chat-' + clubId)
        }
    },
    [clubsModule.actions.membership.unsubscribeFromAll](store) {
        let memberships = store.state.myMemberships
        for (let clubId in memberships) {
            store.dispatch(clubsModule.actions.membership.unsubscribe, clubId)
        }
    }
}

export default actions
