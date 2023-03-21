import chatModule from '@chat/module/store/type'

export default e => {
    let message = e.messageData
    console.log('[Message listener] Message sent', e, {message})

    store.commit(chatModule.mutations.message.update, {
        eventId: message.event_id,
        message
    })

    store.dispatch(chatModule.actions.conversations.update, {
        eventId: message.event_id,
        message,
        isNewUnreadMessage: false,
        insertIfMissing: false
    })
}
