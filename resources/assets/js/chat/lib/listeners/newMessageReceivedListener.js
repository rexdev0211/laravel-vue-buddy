import chatModule from '@chat/module/store/type'
import { conversationsFullyScrolled, scrollToLastMessage, beep } from '@chat/lib/helpers';

export default e => {
    let conversation = e.conversationData;
    let message = conversation.message;
    let interlocutorId = conversation.interlocutor.id;
    let interlocutorToken = conversation.interlocutor.link || conversation.interlocutor.id;
    let chatType = conversation.chatType;

    console.log('[Message listener] Signal received', e);

    // Remove cached chat images from memory
    if (message.msg_type === 'image') {
        store.commit(chatModule.mutations.messages.deleteImages, { userId: interlocutorId })
    } else if (message.msg_type === 'video') {
        store.commit(chatModule.mutations.messages.deleteVideos, { userId: interlocutorId })
    }

    // Update or unshift conversation
    store.dispatch(chatModule.actions.conversations.update, {
        tracker: '[Message listener] Signal received',
        userId: interlocutorId,
        conversation,
        isNewUnreadMessage: true,
        insertIfMissing: true
    })

    // Push message
    store.commit(chatModule.mutations.message.push, {
        userId: interlocutorId,
        message
    })

    //the user is chatting now with sender
    if (
        (app.isMobile && app.$route.path === '/chat/' + interlocutorToken)
        ||
        (app.isDesktop && store.state.chatModule.modal.user && store.state.chatModule.modal.user.id === interlocutorId)
    ) {
        store.dispatch(chatModule.actions.conversations.markAsRead, {
            userId: interlocutorId,
            chatType,
            sync: true
        });
    }

    let mobilePaths = ['/chat', '/chat/unread', '/chat/favorites', '/chat/' + interlocutorId, '/chat/' + interlocutorToken];
    let chatDropdownHidden = $('#chat-dropdown').attr('aria-hidden') === 'true';
    let chatDropDownTabVisible = $('#b-chat__chat').is(':visible');
    let notChattingWithSender =
        !store.state.chatModule.modal.user
        ||
        store.state.chatModule.modal.user.id !== interlocutorId;

    // Bottom bar notification icon
    if (
        (
            (app.isMobile && !mobilePaths.includes(app.$route.path))
            || 
            (app.isDesktop && (chatDropdownHidden || !chatDropDownTabVisible))
        )
        &&
        notChattingWithSender
    ) {
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