import chatModule from '@chat/module/store/type'
import { conversationsFullyScrolled, scrollToLastMessage, beep } from '@chat/lib/helpers';

export default e => {
    let conversation = e.conversationData;
    let message = conversation.message;
    let interlocutorId = conversation.interlocutor.id;
    let interlocutorToken = conversation.interlocutor.link || conversation.interlocutor.id;
    let eventId = conversation.event.id;

    console.log('[Event message listener] Signal received', e);

    // Remove cached chat images from memory
    if (message.msg_type === 'image') {
        store.commit(chatModule.mutations.messages.deleteImages, {
            userId: interlocutorId,
            eventId,
        })
    } else if (message.msg_type === 'video') {
        store.commit(chatModule.mutations.messages.deleteVideos, {
            userId: interlocutorId,
            eventId,
        })
    }

    // Update or unshift conversation
    store.dispatch(chatModule.actions.conversations.update, {
        tracker: '[Event message listener] Signal received',
        eventId,
        userId: interlocutorId,
        conversation,
        isNewUnreadMessage: true,
        insertIfMissing: true
    })

    // Push message
    store.commit(chatModule.mutations.message.push, {
        userId: interlocutorId,
        eventId,
        message
    });

    if(
        (app.isMobile && app.$route.path === `/chat/event-user/${message.event_id}/${interlocutorToken}`)
        || 
        (
            app.isDesktop
            && 
            (store.state.chatModule.modal.user && store.state.chatModule.modal.user.id === message.user_from)
            && 
            (store.state.chatModule.modal.event && store.state.chatModule.modal.event.id === message.event_id)
        )
    ){
        store.dispatch(chatModule.actions.conversations.markAsRead, {
            eventId: message.event_id,
            chatType: conversation.chatType,
            userId: interlocutorId,
            sync: true
        });
    }

    let mobilePaths = ['/chat', `/chat/event-user/${eventId}/${interlocutorId}`, `/chat/event-user/${eventId}/${interlocutorToken}`];
    let chatDropdownHidden = $('#chat-dropdown').attr('aria-hidden') === 'true';
    let chatDropDownTabVisible = $('#b-chat__chat').is(':visible');
    let notChattingWithSender =
        !store.state.chatModule.modal.user
        ||
        store.state.chatModule.modal.user.id !== interlocutorId
        ||
        !store.state.chatModule.modal.event
        ||
        store.state.chatModule.modal.event.id !== eventId;

    // Bottom bar notification icon
    if (
        (
            (app.isMobile && !mobilePaths.includes(app.$route.path))
            ||
            (app.isDesktop && (chatDropdownHidden || !chatDropDownTabVisible))
        )
        &&
        notChattingWithSender
    ){
        store.commit('updateUser', {has_new_messages: true})
    } else {
        axios.post('/api/updateUser', {has_new_messages: false})
    }

    const wasAtBottom = conversationsFullyScrolled(conversation.chatType);

    if (wasAtBottom) {
        setTimeout(() => {
            scrollToLastMessage(conversation.chatType);
        }, 50)
    }

    beep();
}