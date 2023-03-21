import chatModule from '@chat/module/store/type'

export default e => {
    let message = e.messageData
    console.log('[Message listener] Message received', e, message)

    store.commit(chatModule.mutations.message.update, {
        userId: message.user_to,
        eventId: message.event_id,
        message
    })

    store.dispatch(chatModule.actions.conversations.update, {
        tracker: '[Message listener] Message received',
        userId: message.user_to,
        eventId: message.event_id,
        message,
        isNewUnreadMessage: false,
        insertIfMissing: false
    })
}
