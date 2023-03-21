import _ from "lodash";
import chatModule from "./type";
import {convertUtcToLocal, sortConversationsFunc} from "@chat/lib/helpers";
import discoverModule from "../../../discover/module/store/type";

const mutations = {
    [chatModule.mutations.toggleSwipe](state, value) {
        state.swipeEnabled = value
    },

    // resetTemporaryState
    [chatModule.mutations.reset](state) {
        state.chat.user.messages = {};
        state.chat.user.images = {};
        state.chat.user.videos = {};
        state.chat.event.messages = {};
        state.chat.event.images = {};
        state.chat.event.videos = {};
    },
    // updateChatModal
    [chatModule.mutations.modal](state, payload) {
        state.modal = {...state.modal, ...payload}
    },
    // addChatMessage
    [chatModule.mutations.message.push](state, { userId, eventId, message }) {
        message.idateOriginal = message.idate
        message.idate = convertUtcToLocal(message.idate)

        if (userId && !eventId) {
            if (state.chat.user.messages[userId] === undefined) {
                Vue.set(state.chat.user.messages, userId, [])
            }
            state.chat.user.messages[userId].push(message);

        } else if (userId && eventId) {
            let id = `${eventId}-${userId}`;
            if (state.chat.event.messages[id] === undefined) {
                Vue.set(state.chat.event.messages, id, []);
            }
            state.chat.event.messages[id].push(message);

        } else {
            let id = eventId;
            if (state.chat.event.messages[eventId] === undefined) {
                Vue.set(state.chat.event.messages, id, []);
            }
            state.chat.event.messages[id].push(message);
        }
    },
    // prependChatMessages
    [chatModule.mutations.messages.set](state, { userId, eventId, messages }) {
        messages.forEach((el) => {
            el.idate = convertUtcToLocal(el.idate)
        })

        if (userId && !eventId) {
            Vue.set(state.chat.user.messages, userId, messages)
        } else if (userId && eventId) {
            Vue.set(state.chat.event.messages, `${eventId}-${userId}`, messages)
        } else {
            Vue.set(state.chat.event.messages, eventId, messages)
        }
    },
    // prependChatMessages
    [chatModule.mutations.messages.unshift](state, { userId, eventId, messages }) {
        messages = messages.map((el) => {
            el.idateOriginal = el.idate
            el.idate = convertUtcToLocal(el.idate)
            return el
        })

        if (userId && !eventId && messages.length) {
            if (state.chat.user.messages[userId] === undefined) {
                Vue.set(state.chat.user.messages, userId, [])
            }
            state.chat.user.messages[userId].unshift(...messages);

        } else if (userId && eventId && messages.length) {
            let id = `${eventId}-${userId}`
            if (state.chat.event.messages[id] === undefined) {
                Vue.set(state.chat.event.messages, id, [])
            }
            state.chat.event.messages[id].unshift(...messages);

        } else {
            let id = eventId
            if (state.chat.event.messages[id] === undefined) {
                Vue.set(state.chat.event.messages, id, [])
            }
            state.chat.event.messages[id].unshift(...messages);
        }
    },
    // updateChatMessage
    [chatModule.mutations.message.update](state, { userId, eventId, message }) {
        let messageGroup = null
        let messageSubIndex = null

        if (userId && !eventId) {
            messageGroup = 'user'
            messageSubIndex = userId
        } else if (userId && eventId) {
            messageGroup = 'event'
            messageSubIndex = `${eventId}-${userId}`
        } else {
            messageGroup = 'event'
            messageSubIndex = eventId
        }

        for (let index in state.chat[messageGroup].messages[messageSubIndex]) {
            let existingMessage = state.chat[messageGroup].messages[messageSubIndex][index]
            if (
                (
                    existingMessage.id === message.id
                    ||
                    (existingMessage.hash && message.hash && existingMessage.hash === message.hash)
                )
                &&
                !existingMessage.cancelled
            ) {
                //console.log('Found message to update', { message, existingMessage, index })

                let newMessage = _.cloneDeep(message)
                delete newMessage.hash

                newMessage.idateOriginal = newMessage.idate
                newMessage.idate = convertUtcToLocal(newMessage.idate)
                Vue.set(state.chat[messageGroup].messages[messageSubIndex], index, newMessage)

                break
            }
        }
    },
    // setChatImages
    [chatModule.mutations.messages.setImages](state, payload) {
        Vue.set(state.chat.user.images, payload.userId, payload.images);
    },
    // chatModule.mutations.messages.setVideos
    [chatModule.mutations.messages.setVideos](state, payload) {
        Vue.set(state.chat.user.videos, payload.userId, payload.videos);
    },
    // setEventChatImages
    [chatModule.mutations.messages.setImagesEvent](state, { userId, eventId, images }) {
        let id = null
        if (userId && eventId) {
            id = `${eventId}-${userId}`
        } else if (!userId && eventId){
            id = eventId
        }
        Vue.set(state.chat.event.images, id, images);
    },
    // setEventChatVideos
    [chatModule.mutations.messages.setVideosEvent](state, { userId, eventId, videos }) {
        let id = null
        if (userId && eventId) {
            id = `${eventId}-${userId}`
        } else if (!userId && eventId){
            id = eventId
        }
        Vue.set(state.chat.event.videos, id, videos);
    },
    // deleteChatImages
    [chatModule.mutations.messages.deleteImages](state, { userId, eventId }) {
        if (userId && !eventId) {
            Vue.delete(state.chat.user.images, userId)
        } else if (userId && eventId) {
            Vue.delete(state.chat.event.images, `${eventId}-${userId}`)
        } else {
            Vue.delete(state.chat.event.images, eventId)
        }
    },
    // deleteChatVideos
    [chatModule.mutations.messages.deleteVideos](state, { userId, eventId }) {
        if (userId && !eventId) {
            Vue.delete(state.chat.user.videos, userId);
        } else if (userId && eventId) {
            Vue.delete(state.chat.event.videos, `${eventId}-${userId}`)
        } else {
            Vue.delete(state.chat.event.videos, eventId)
        }
    },

    [chatModule.mutations.conversations.afterUpdate](state) {
        state.conversations.all = state.conversations.all.sort(sortConversationsFunc)
        state.conversations.unread = state.conversations.unread.sort(sortConversationsFunc)
        state.conversations.favorites = state.conversations.favorites.sort(sortConversationsFunc)
    },
    [chatModule.mutations.conversations.addLoadingChat](state, { url }) {
        let loadingChats = state.loadingChats;
        let urlIndex = _.indexOf(loadingChats, url);

        if (urlIndex !== -1) {
            state.loadingChats.splice(urlIndex, 1);
        }

        state.loadingChats.push(url);
    },
    [chatModule.mutations.conversations.removeLoadingChat](state, { url }) {
        let loadingChats = state.loadingChats
        let index = _.indexOf(loadingChats, url)

        if (index !== -1) {
            state.loadingChats.splice(index, 1)
        }
    },
    // sortConversationsGroup
    [chatModule.mutations.conversations.sortGroup](state, group) {
        return state.conversations[group].sort(sortConversationsFunc)
    },
    [chatModule.mutations.conversations.clearGroup](state, group) {
        state.conversations[group] = []
    },
    // addConversations
    [chatModule.mutations.conversations.push](state, {conversations, group}) {
        if (conversations.length) {
            conversations = conversations.map(el => {
                el.message.idate = convertUtcToLocal(el.message.idate)
                return el
            });

            let sameCount = [];

            if (conversations.length && state.conversations[group].length) {
                conversations.forEach((obj, key) => {
                    console.log('conversations[key]');
                    console.log(conversations[key].message.message);
                    console.log(state.conversations[group][key].message.message);
                    console.log('------');

                    if (conversations[key].message.message === state.conversations[group][key].message.message) {
                        sameCount.push(true);
                    }
                });

                // state.conversations[group] = [];
            }

            if (sameCount.length !== conversations.length) {
                state.conversations[group].push(...conversations)
            } else {
                console.log('found conversations duplicates, skipping, params: '+sameCount.length+'/'+conversations.length);
            }
        }
    },
    [chatModule.mutations.conversations.pushUnblockedConversations](state, {conversations}) {
        let groups = ['all', 'unread', 'favorites']

        conversations = conversations.map(el => {
            el.message.idate = convertUtcToLocal(el.message.idate)
            return el
        })

        groups.forEach((group) => {
            if (group === 'all') {
                state.conversations[group].push(...conversations)
            } else if (group === 'unread') {
                for (let i = 0; i < conversations.length; i++) {
                    console.log(conversations[i])
                    if (conversations[i].unreadMessagesCount > 0) {
                        state.conversations[group].push(conversations[i])
                    }
                }
            } else if (group === 'favorites') {
                for (let i = 0; i < conversations.length; i++) {
                    if (conversations[i].interlocutor.isFavorite && conversations[i].chatType !== 'event') {
                        state.conversations[group].push(conversations[i])
                    }
                }
            }
        });
    },
    [chatModule.mutations.conversations.unshift](state, {conversations, group}) {
        if (conversations.length) {
            conversations = conversations.map(el => {
                el.message.idateOriginal = el.message.idate
                el.message.idate = convertUtcToLocal(el.message.idate)
                return el
            });
            state.conversations[group].unshift(...conversations);
        }
    },
    // updateConversation
    [chatModule.mutations.conversations.update](state, { tracker, userId, eventId, interlocutor, message, conversation, isNewUnreadMessage, insertIfMissing }) {
        let conversationsGroups = ['all', 'unread', 'favorites']

        conversationsGroups.forEach(function(group){
            // Do not update/insert event and group messages in 'favorites' group
            if (eventId && group === 'favorites') {
                return
            }

            // Search a conversation
            let conversationIndex = -1
            if (userId && !eventId) {
                conversationIndex = state.conversations[group].findIndex(el => {
                    return el.chatType === 'user' && el.interlocutor.id == userId
                })
            } else if (userId && eventId) {
                conversationIndex = state.conversations[group].findIndex(el => {
                    return el.chatType === 'event' && el.event.id == eventId && el.interlocutor.id == userId
                })
            } else {
                conversationIndex = state.conversations[group].findIndex(el => {
                    return el.chatType === 'group' && el.event.id == eventId
                })
            }

            // Create a conversation
            if (conversationIndex === -1) {
                if (
                    insertIfMissing
                    &&
                    (
                        group === 'all'
                        ||
                        group === 'unread'
                        &&
                        isNewUnreadMessage
                        ||
                        (group === 'favorites' && conversation.interlocutor.isFavorite)
                    )
                ) {
                    // If no conversation presented - add the new conversation to a group
                    let newConversation = _.cloneDeep(conversation)

                    let idate = newConversation.message.idate
                    if (
                        _.isString(idate)
                        &&
                        idate.indexOf('T') !== -1
                    ) {
                        newConversation.message.idate = convertUtcToLocal(idate)
                    }

                    // Increment unread messages count
                    newConversation.unreadMessagesCount = isNewUnreadMessage ? 1 : 0

                    // Insert new conversation
                    state.conversations[group].unshift(newConversation)
                }
                return
            }

            // Existing conversation which going to be modified
            let existingConversation = _.cloneDeep(state.conversations[group][conversationIndex])

            // Update the message
            let conversationMessage = _.get(conversation, 'message', {})
            if (
                !_.isEmpty(message)
                ||
                !_.isEmpty(conversationMessage)
            ) {
                let newMessage = _.cloneDeep({
                    ...existingConversation.message,
                    ...conversationMessage,
                    ...message
                })

                if (
                    !newMessage.idate
                    ||
                    !newMessage.msg_type
                    ||
                    (newMessage.msg_type === 'text' && !newMessage.message)
                ) {
                    const payload = {
                        tracker,
                        userId,
                        eventId,
                        interlocutor,
                        message,
                        conversation,
                        isNewUnreadMessage,
                        insertIfMissing,
                        newMessage,
                    }
                    throw new Error('Malformed message: ' + JSON.stringify(payload));
                }

                // Convert message's idate if it needs
                if (
                    _.isString(newMessage.idate)
                    &&
                    newMessage.idate.indexOf('T') !== -1
                ) {
                    console.log('Conversation message idate updated', {
                        idateOriginal: newMessage.idate,
                        idate: convertUtcToLocal(newMessage.idate)
                    })
                    newMessage.idateOriginal = newMessage.idate
                    newMessage.idate = convertUtcToLocal(newMessage.idate)
                }

                // Modify conversation's caption message
                existingConversation.message = newMessage

                // Increment unread messages
                if (isNewUnreadMessage) {
                    existingConversation.unreadMessagesCount++
                }
            }

            // Update the interlocutor
            if (!_.isEmpty(interlocutor)) {
                existingConversation.interlocutor = _.cloneDeep({...existingConversation.interlocutor, ...interlocutor})
            }

            Vue.set(state.conversations[group], conversationIndex, existingConversation)
        })
    },
    [chatModule.mutations.conversations.updateConversationEvent](state, { eventId, eventData }) {
        let conversationsGroups = ['all', 'unread']
        conversationsGroups.forEach(function(group) {
            state.conversations[group].forEach((conversation, conversationIndex) => {
                if (
                    conversation.chatType === 'event'
                    &&
                    conversation.event.id == eventId
                ) {
                    conversation.event = {...conversation.event, ...eventData}
                    Vue.set(state.conversations[group], conversationIndex, conversation)
                }
            });
        })
    },
    // markConversationAsRead
    [chatModule.mutations.conversations.markAsRead](state, { userId, eventId, chatType }) {
        let conversationGroups = ['all', 'unread', 'favorites']
        conversationGroups.forEach(function(group){
            let conversationIndex = -1
            if (userId && !eventId && chatType === 'user') {
                conversationIndex = state.conversations[group].findIndex(el => el.chatType === chatType && el.interlocutor.id == userId);
            } else if (userId && eventId && chatType === 'event') {
                conversationIndex = state.conversations[group].findIndex(el => el.chatType === chatType && el.event.id == eventId && el.interlocutor.id == userId);
            } else if (eventId && chatType === 'group') {
                conversationIndex = state.conversations[group].findIndex(el => el.chatType === chatType && el.event.id == eventId)
            } else {
                return
            }

            if (conversationIndex !== -1) {
                if (group === 'unread') {
                    Vue.delete(state.conversations[group], conversationIndex)
                } else {
                    state.conversations[group][conversationIndex].unreadMessagesCount = 0
                }
            }

            let userIndex = -1;
            let users = store.getters[discoverModule.getters.usersAround];

            if (userId && users) {
                userIndex = users.findIndex(el => el.id === userId);
            }

            if (userIndex !== -1) {
                let user = users[userIndex];
                user.unreadMessagesCount = 0;
                Vue.set(users, userIndex, user);
            }

        })
    },

    // removeConversation
    [chatModule.mutations.conversations.remove](state, { userId, eventId }) {
        let conversationGroups = ['all', 'unread', 'favorites']
        conversationGroups.forEach(function(group){
            state.conversations[group].forEach((el, index) => {
                if (
                    (!eventId && userId && el.chatType === 'user' && el.interlocutor.id == userId)
                    ||
                    (eventId && userId && el.chatType === 'event' && el.interlocutor.id == userId && el.event.id == eventId)
                    ||
                    (eventId && !userId && el.chatType === 'event' && el.event.id == eventId)
                    ||
                    (eventId && !userId && el.chatType === 'group' && el.event.id == eventId)
                ) {
                    Vue.delete(state.conversations[group], index)
                }
            })
        })

        if (userId && !eventId) {
            Vue.delete(state.chat.user.messages, userId)
        } else if (userId && eventId){
            Vue.delete(state.chat.event.messages, `${eventId}-${userId}`)
        }
    },
    [chatModule.mutations.conversations.removeAllUserBlockedChats](state, {userId}) {
        let conversationGroups = ['all', 'unread', 'favorites']
        conversationGroups.forEach(function (group) {
            let toDel = [];
            state.conversations[group].forEach((el, index) => {
                if (
                    (el.chatType === 'user' || el.chatType === 'event')
                    &&
                    el.interlocutor.id === userId
                ) {
                    toDel.push(index)
                }
            })
            let conversation = state.conversations[group];
            state.conversations[group] = conversation.filter((item, index) => {
                return !toDel.includes(index);
            });
        })
    },
    [chatModule.mutations.conversations.clearHiddenChats](state) {
        state.hiddenChats = [];
    },
    [chatModule.mutations.conversations.hide](state, { currentConversation }) {
        if (state.hiddenChats.length > 0) {
            state.hiddenChats = [];
        }

        state.hiddenChats.push(currentConversation);
    }
}

export default mutations
