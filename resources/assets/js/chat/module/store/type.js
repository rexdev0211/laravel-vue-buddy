const chatModule = {
    actions: {
        toggleSwipe:'chat/actions/toggleSwipe',

        conversations: {
            loadGroup: 'chat/actions/conversations/loadGroup',
            clearGroup: 'chat/actions/conversations/clearGroup',

            push: 'chat/actions/conversations/push',
            unshift: 'chat/actions/conversations/unshift',

            update: 'chat/actions/conversations/update',
            updateConversationEvent: 'chat/actions/conversations/updateConversationEvent',

            markAsRead: 'chat/actions/conversations/markAsRead',
            remove: 'chat/actions/conversations/remove',
            removeAllUserBlockedChats: 'chat/actions/conversations/removeAllUserBlockedChats',
            pushUnblockedConversations: 'chat/actions/conversations/pushUnblockedConversations'
        },
        messages: {
            load: 'chat/actions/messages/load',
            loadEvent: 'chat/actions/messages/loadEvent',
            loadGroup: 'chat/actions/messages/loadGroup',
            delete: 'chat/actions/messages/delete',
        },
    },
    mutations: {
        reset: 'chat/mutations/reset',
        modal: 'chat/mutations/modal',
        toggleSwipe:'chat/mutations/toggleSwipe',

        conversations: {
            afterUpdate: 'chat/mutations/conversations/afterUpdate',

            addLoadingChat: 'chat/mutations/conversations/addLoadingChat',
            removeLoadingChat: 'chat/mutations/conversations/removeLoadingChat',

            clearGroup: 'chat/mutations/conversations/clearGroup',
            sortGroup: 'chat/mutations/conversations/sortGroup',

            push: 'chat/mutations/conversations/push',
            unshift: 'chat/mutations/conversations/unshift',

            update: 'chat/mutations/conversations/update',
            updateConversationEvent: 'chat/mutations/conversations/updateConversationEvent',

            markAsRead: 'chat/mutations/conversations/markAsRead',
            remove: 'chat/mutations/conversations/remove',
            removeAllUserBlockedChats: 'chat/mutations/conversations/removeAllUserBlockedChats',
            pushUnblockedConversations: 'chat/mutations/conversations/pushUnblockedConversations',

            clearHiddenChats: 'chat/mutations/conversations/clearHiddenChats',
            hide: 'chat/mutations/conversations/hide'
        },
        messages: {
            unshift: 'chat/mutations/messages/unshift',
            set: 'chat/mutations/messages/set',

            setImages: 'chat/mutations/messages/setImages',
            setVideos: 'chat/mutations/messages/setVideos',
            setImagesEvent: 'chat/mutations/messages/setImagesEvent',
            setVideosEvent: 'chat/mutations/messages/setVideosEvent',

            deleteImages: 'chat/mutations/messages/deleteImages',
            deleteVideos: 'chat/mutations/messages/deleteVideos',

            checkMessage: 'chat/mutations/messages/checkMessage'
        },
        message: {
            push: 'chat/mutations/message/push',
            update: 'chat/mutations/message/update',
        },
    },
    getters: {
        conversations: {
            all: 'chat/getters/conversations/all',
            unread: 'chat/getters/conversations/unread',
            favorites: 'chat/getters/conversations/favorites',
            userIds: 'chat/getters/conversations/userIds',
            loadingChats: 'chat/getters/conversations/loadingChats'
        },
        messages: {
            count: {
                unread: 'chat/getters/messages/count/unread',
                unreadEvent: 'chat/getters/messages/count/unreadEvent',
            }
        }
    }
}

export default chatModule