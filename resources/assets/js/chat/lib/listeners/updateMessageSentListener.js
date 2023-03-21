import chatModule from '@chat/module/store/type'

export default e => {
    let message = e.messageData
    console.log('[Message listener] Message sent', e, {message})

    store.commit(chatModule.mutations.message.update, {
        userId: message.user_from,
        eventId: message.event_id,
        message
    })

    store.dispatch(chatModule.actions.conversations.update, {
        tracker: '[Message listener] Message sent',
        userId: message.user_from,
        eventId: message.event_id,
        message,
        isNewUnreadMessage: false,
        insertIfMissing: false
    })
}
