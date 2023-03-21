import _ from "lodash";
import chatModule from "./type";

const actions = {
    [chatModule.actions.toggleSwipe](store, payload) {
        store.commit(chatModule.mutations.toggleSwipe, payload)
    },
    async [chatModule.actions.conversations.loadGroup](store, { group, limit, page }) {
        let response = await axios.get('/api/getConversations', { params: { group, page, limit }})
        if (response.status === 200) {
            let data = response.data
            if (data[group] && _.toArray(data[group]).length) {
                store.dispatch(chatModule.actions.conversations.push, {
                    conversations: _.toArray(data[group]),
                    group
                })
            }
            return _.toArray(data[group]).length
        }
    },
    [chatModule.actions.conversations.clearGroup](store, payload){
        store.commit(chatModule.mutations.conversations.clearGroup, payload)
        store.commit(chatModule.mutations.conversations.afterUpdate)
    },
    [chatModule.actions.conversations.push](store, payload){
        store.commit(chatModule.mutations.conversations.push, payload)
        store.commit(chatModule.mutations.conversations.afterUpdate)
    },
    [chatModule.actions.conversations.pushUnblockedConversations](store, payload) {
        store.commit(chatModule.mutations.conversations.pushUnblockedConversations, payload)
        store.commit(chatModule.mutations.conversations.afterUpdate);
    },
    [chatModule.actions.conversations.unshift](store, payload){
        store.commit(chatModule.mutations.conversations.unshift, payload)
        store.commit(chatModule.mutations.conversations.afterUpdate)
    },

    [chatModule.actions.conversations.update](store, payload){
        store.commit(chatModule.mutations.conversations.update, payload)
        store.commit(chatModule.mutations.conversations.afterUpdate)
    },
    [chatModule.actions.conversations.updateConversationEvent](store, payload){
        store.commit(chatModule.mutations.conversations.updateConversationEvent, payload)
        store.commit(chatModule.mutations.conversations.afterUpdate)
    },
    [chatModule.actions.conversations.markAsRead](store, { userId, eventId, chatType, sync }){
        store.commit(chatModule.mutations.conversations.markAsRead, { userId, eventId, chatType })
        store.commit(chatModule.mutations.conversations.afterUpdate)

        if (sync) {
            if (!!userId && !eventId) {
                axios.post(`/api/markConversationAsRead/${userId}`);
            } else if (!!userId && !!eventId) {
                axios.post(`/api/markEventConversationAsRead/${eventId}/${userId}`)
            }
        }
    },
    [chatModule.actions.conversations.remove](store, payload){
        store.commit(chatModule.mutations.conversations.remove, payload)
        store.commit(chatModule.mutations.conversations.afterUpdate)
    },
    [chatModule.actions.conversations.removeAllUserBlockedChats](store, payload) {
        store.commit(chatModule.mutations.conversations.removeAllUserBlockedChats, payload)
        store.commit(chatModule.mutations.conversations.afterUpdate)
    },
    // loadMessages
    async [chatModule.actions.messages.load](store, payload) {
        let userId = payload.userId
        let maxTimestamp = 0
        let messages = []
        let loadingUrls = store.getters[chatModule.getters.conversations.loadingChats]

        if (
            !_.isUndefined(store.state.chat.user.messages[userId])
            &&
            !_.isUndefined(store.state.chat.user.messages[userId][0])
        ) {
            maxTimestamp = store.state.chat.user.messages[userId][0].idateOriginal
        }

        const messageUrl = `/api/getMessages/${userId}`;
        const findIndex = _.indexOf(loadingUrls, messageUrl)

        if (findIndex !== -1) {
            return [];
        } else {
            store.commit(chatModule.mutations.conversations.addLoadingChat, {
                url: messageUrl
            })
        }

        try {
            let response = await axios.get(`/api/getMessages/${userId}?maxTimestamp=${maxTimestamp}`)
            if (response.status === 200) {

                store.commit(chatModule.mutations.conversations.removeLoadingChat, {
                    url: messageUrl
                })

                let data = response.data
                messages = _.toArray(data.messages);

                store.commit(chatModule.mutations.messages.unshift, {
                    messages,
                    userId
                })
            }
        } catch (error) {
            console.error(error)
        }

        return messages
    },
    // loadEventMessages
    async [chatModule.actions.messages.loadEvent](store, payload) {
        let eventId = payload.eventId
        let userId = payload.userId
        let chatHistoryId = `${payload.eventId}-${payload.userId}`
        let maxTimestamp = ''
        let messages = []
        let loadingUrls = store.getters[chatModule.getters.conversations.loadingChats]

        if (
            !_.isUndefined(store.state.chat.event.messages[chatHistoryId])
            &&
            !_.isUndefined(store.state.chat.event.messages[chatHistoryId][0])
        ) {
            maxTimestamp = store.state.chat.event.messages[chatHistoryId][0].idateOriginal
        }

        const messageUrl = `/api/getEventMessages/${eventId}/${userId}`;
        const findIndex = _.indexOf(loadingUrls, messageUrl)

        if (findIndex !== -1) {
            return [];
        } else {
            store.commit(chatModule.mutations.conversations.addLoadingChat, {
                url: messageUrl
            })
        }

        try {
            let response = await axios.get(`/api/getEventMessages/${eventId}/${userId}?maxTimestamp=${maxTimestamp}`)
            if (response.status === 200) {
                store.commit(chatModule.mutations.conversations.removeLoadingChat, {
                    url: messageUrl
                })

                let data = response.data
                messages = _.toArray(data.messages)

                store.commit(chatModule.mutations.messages.unshift, {
                    messages,
                    eventId,
                    userId
                })
            }
        } catch (error) {
            console.error(error)
        }

        return messages
    },
    async [chatModule.actions.messages.loadGroup](store, payload) {
        let eventId = payload.eventId
        let chatHistoryId = payload.eventId
        let maxTimestamp = 0
        let messages = []
        let loadingUrls = store.getters[chatModule.getters.conversations.loadingChats]

        if (
            !_.isUndefined(store.state.chat.event.messages[chatHistoryId])
            &&
            !_.isUndefined(store.state.chat.event.messages[chatHistoryId][0])
        ) {
            maxTimestamp = store.state.chat.event.messages[chatHistoryId][0].idateOriginal
        }

        const messageUrl = `/api/getGroupMessages/${eventId}`;
        const findIndex = _.indexOf(loadingUrls, messageUrl)

        if (findIndex !== -1) {
            return [];
        } else {
            store.commit(chatModule.mutations.conversations.addLoadingChat, {
                url: messageUrl
            })
        }

        try {
            let response = await axios.get(`/api/getGroupMessages/${eventId}?maxTimestamp=${maxTimestamp}`)
            if (response.status === 200) {
                store.commit(chatModule.mutations.conversations.removeLoadingChat, {
                    url: messageUrl
                })

                let data = response.data
                messages = _.toArray(data.messages)

                store.commit(chatModule.mutations.messages.unshift, {
                    messages,
                    eventId
                })
            }
        } catch (error) {
            console.error(error)
        }

        return messages
    },
    // tryDeleteChatMessage
    [chatModule.actions.messages.delete](store, {userId, eventId, messageId}) {
        if (store.rootState.userIsPro) {
            axios.post('/api/markMessageAsRemoved', { messageId })
                .then((response) => {
                    if (response.status === 200) {
                        let data = response.data
                        store.commit(chatModule.mutations.message.update, {
                            userId,
                            eventId,
                            message: data.message
                        })
                        store.dispatch(chatModule.actions.conversations.update, {
                            tracker: 'markMessageAsRemoved',
                            userId,
                            eventId,
                            message: data.message,
                            isNewUnreadMessage: false,
                            insertIfMissing: false
                        })
                    }
                })
        } else {
            store.dispatch('requirementsAlertShow', 'deletemsg')
        }
    },
}

export default actions