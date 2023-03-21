import chatModule          from '@chat/module/store/type'
import discoverModule      from "@discover/module/store/type";
import eventsModule        from '@events/module/store/type';
import notificationsModule from '@notifications/module/store/type';

export default e => {
    console.log('[User Blocked Listener]', e);

    store.commit(notificationsModule.mutations.visitors.set, [])
    store.commit(notificationsModule.mutations.visited.set, [])
    store.commit(notificationsModule.mutations.notifications.set, [])
    store.commit(chatModule.mutations.conversations.clearGroup, 'all')
    store.commit(chatModule.mutations.conversations.clearGroup, 'unread')
    store.commit(chatModule.mutations.conversations.clearGroup, 'favorites')
    store.commit(eventsModule.mutations.resetPagination)
    store.commit(eventsModule.mutations.events.set, [])

    Vue.nextTick(async () => {
        await store.dispatch(notificationsModule.actions.visitors.load)
        await store.dispatch(notificationsModule.actions.visited.load)
        await store.dispatch(notificationsModule.actions.notifications.load)
        await store.dispatch(chatModule.actions.conversations.loadGroup, {
            page: 0,
            limit: window.LOAD_CHAT_WINDOWS_LIMIT,
            group: 'all'
        })
        await store.dispatch(eventsModule.actions.events.load)
        await store.dispatch('loadCurrentUserInfo')
    })
    
    if (e.type === 'block') {
        store.dispatch(chatModule.actions.conversations.removeAllUserBlockedChats, {
            userId: e.userId
        })
        store.dispatch(discoverModule.actions.users.remove, { userId: e.userId })
    } else if (e.type === 'unblock') {
        store.dispatch(discoverModule.actions.users.reload)
    }
}
