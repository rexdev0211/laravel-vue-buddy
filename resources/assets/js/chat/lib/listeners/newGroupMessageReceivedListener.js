import chatModule from '@chat/module/store/type'
import auth from '@general/lib/auth';
import { conversationsFullyScrolled, scrollToLastMessage, beep } from '@chat/lib/helpers';

export default e => {
    let conversation = e.conversationData;
    let broadcasting = e.broadcastingData;

    let currentUserId = auth.getUserId();
    let message = conversation.message;
    let eventId = conversation.event.id;

    if ((broadcasting.ignore_recipient_id || []).includes(currentUserId)) {
        return
    }

    console.log('[Group message listener] Signal received', e);

    // Remove cached chat images from memory
    if (message.msg_type === 'image') {
        store.commit(chatModule.mutations.messages.deleteImages, {
            eventId
        })
    } else if (message.msg_type === 'video') {
        store.commit(chatModule.mutations.messages.deleteVideos, {
            eventId
        })
    }

    // Update or unshift conversation
    store.dispatch(chatModule.actions.conversations.update, {
        eventId,
        conversation,
        isNewUnreadMessage: !['joined', 'left'].includes(message.msg_type),
        insertIfMissing: true
    })

    // Push message
    store.commit(chatModule.mutations.message.push, {
        eventId,
        message
    });

    if (
        (app.isMobile && app.$route.path === `/chat/group/${eventId}`)
        ||
        (
            app.isDesktop
            &&
            (store.state.chatModule.modal.event && store.state.chatModule.modal.event.id === eventId)
        )
    ) {
        store.dispatch(chatModule.actions.conversations.markAsRead, {
            eventId: eventId,
            chatType: conversation.chatType,
            userId:  currentUserId,
            sync:    true
        });
    }

    let mobilePaths = ['/chat', `/chat/event/${eventId}`];
    let chatDropdownHidden = $('#chat-dropdown').attr('aria-hidden') === 'true';
    let chatDropDownTabVisible = $('#b-chat__chat').is(':visible');
    let notChattingWithSender =
        !store.state.chatModule.modal.event
        ||
        store.state.chatModule.modal.event.id !== eventId;

    // Bottom bar notification icon
    if (
        !['joined', 'left'].includes(message.msg_type)
        &&
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
        // axios.post('/api/updateUser', {has_new_messages: false})
        // store.dispatch(chatModule.actions.conversations.markAsRead, {
        //     eventId: eventId,
        //     userId:  currentUserId,
        //     sync:    true
        // });
    }

    const wasAtBottom = conversationsFullyScrolled(conversation.chatType);
    if (wasAtBottom) {
        setTimeout(() => {
            scrollToLastMessage(conversation.chatType);
        }, 50)
    }

    beep();
}
