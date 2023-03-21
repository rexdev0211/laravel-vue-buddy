import chatModule from "./type";

const getters = {
    // conversationsAll
    [chatModule.getters.conversations.userIds]: state => {
        return state.conversations.all
            .map(e => { return (e.interlocutor && e.interlocutor.id) })
            .filter(e => e)
    },
    // conversationsAll
    [chatModule.getters.conversations.all]: state => {
        return state.conversations.all || []
    },
    // conversationsUnread
    [chatModule.getters.conversations.unread]: state => {
        return state.conversations.unread || []
    },
    // conversationsFavorites
    [chatModule.getters.conversations.favorites]: state => {
        return state.conversations.favorites || []
    },
    [chatModule.getters.conversations.loadingChats]: state => {
        return state.loadingChats || []
    },
    // unreadMessagesCount
    [chatModule.getters.messages.count.unread]: state => (userId) => {
        let conversation = state.conversations.all.find(el => el.chatType === 'user' && el.interlocutor.id == userId);
        return (conversation && conversation.unreadMessagesCount) ?
            conversation.unreadMessagesCount
            :
            '';
    },
    // unreadEventMessagesCount
    [chatModule.getters.messages.count.unreadEvent]: state => (eventId, userId) => {
        return state.conversations.all.reduce((accumulator, conversation) => {
            if (
                conversation.chatType === 'event'
                &&
                conversation.event.id == eventId
                &&
                (!!userId ? conversation.interlocutor.id == userId : true)
            ) {
                return accumulator + conversation.unreadMessagesCount
            } else {
                return accumulator
            }
        }, 0);
    },
}

export default getters