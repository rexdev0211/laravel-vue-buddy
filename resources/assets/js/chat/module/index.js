import mutations from "./store/mutations";
import actions from "./store/actions";
import getters from "./store/getters";

/* Module */
const _module = {
    state: () => ({
        swipeEnabled: true,

        loadingChats: [],

        // Visible chat modal/page with user or user+event
        modal: {
            mode: null,
            previewMode: null,
            user: null,
            event: null,
            minimized: false
        },

        // Chat messages by chat type
        chat: {
            user: {
                messages: {},
                images: {},
                videos: {},
            },
            event: {
                messages: {},
                images: {},
                videos: {},
            }
        },

        // To hide chats to the bottom of the screen, for quick access to chats
        hiddenChats: [],

        // Conversations by group
        conversations: {
            all: [],
            unread: [],
            favorites: [],
        },
    }),
    actions,
    mutations,
    getters
}

export default _module