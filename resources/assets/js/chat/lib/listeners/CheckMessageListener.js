import chatModule from '@chat/module/store/type'
import _ from "lodash";
import { scrollToLastMessage } from '@chat/lib/helpers';

export default e => {
        console.log('[Check Message Listener]', e);

        let conversation = e.messageData;
        let chatModuleState = store.state.chatModule;
        let index,
            userTo = conversation.message.user_to,
            eventId = conversation.event?.id,
            conversations,
            messages;

        if (conversation.chatType === 'user') {
                messages = chatModuleState.chat.user.messages[userTo]
                conversations = chatModuleState.conversations.all;
                index = -1;

                if (messages?.length > 0) {
                        console.log('[Check Message listener] messages list', messages);

                        index = _.findIndex(messages, (e) => {
                                return e.id === conversation.message.id;
                        })

                        if (index === -1) {
                                let pushProhibited = false;

                                messages.forEach((message) => {
                                        if (obj.hash && obj.h.match(/gen\:/) && obj.hash == conversation.message.hash) {
                                                pushProhibited = true;
                                        }
                                });

                                if (!pushProhibited) {
                                        store.commit(chatModule.mutations.message.push, {
                                                userId: userTo,
                                                message: conversation.message
                                        })
                                        console.log('[Check Message listener] push allowed');
                                } else {
                                        console.log('[Check Message listener] push prohibited');
                                }
                        }
                }

                index = _.findIndex(conversations, (e) => {
                        return e.chatType === conversation.chatType
                            &&
                            e.interlocutor?.id === conversation.interlocutor.id
                            &&
                            e.message.id === conversation.message.id
                })

                if (index === -1) {
                        store.dispatch(chatModule.actions.conversations.update, {
                                tracker: '[Check Message listener] Signal received',
                                userId: userTo,
                                conversation: conversation,
                                isNewUnreadMessage: false,
                                insertIfMissing: true
                        })
                }

        } else if (conversation.chatType === 'event') {
                conversations = chatModuleState.conversations.all;
                messages = chatModuleState.chat.event.messages[`${eventId}-${userTo}`]
                index = -1;

                if (messages?.length > 0) {
                        console.log('[Check Message listener] messages list');
                        console.log(messages);

                        index = _.findIndex(messages, (e) => {
                                return e.id === conversation.message.id;
                        })

                        if (index === -1) {
                                let pushProhibited = false;

                                messages.forEach((obj) => {
                                        if (obj.hash && obj.hash.match(/gen\:/) && obj.hash == conversation.message.hash) {
                                                pushProhibited = true;
                                        }
                                });

                                if (!pushProhibited) {
                                        store.commit(chatModule.mutations.message.push, {
                                                userId: userTo,
                                                eventId,
                                                message: conversation.message
                                        });
                                        console.log('[Check Message listener] push allowed');
                                } else {
                                        console.log('[Check Message listener] push prohibited');
                                }
                        }
                }

                index = _.findIndex(conversations, (e) => {
                        return e.chatType === conversation.chatType
                            &&
                            e.event?.id === eventId
                            &&
                            e.interlocutor.id === conversation.interlocutor.id
                            &&
                            e.message.id === conversation.message.id;
                })

                if (index === -1) {
                        store.dispatch(chatModule.actions.conversations.update, {
                                tracker: '[Check Message listener] Signal received',
                                eventId,
                                userId: userTo,
                                conversation,
                                isNewUnreadMessage: false,
                                insertIfMissing: true
                        })


                }

        } else if (conversation.chatType === 'group') {
                conversations = chatModuleState.conversations.all;
                messages = chatModuleState.chat.event.messages[eventId];

                if (messages?.length > 0) {
                        console.log('[Check Message listener] messages list');
                        console.log(messages);

                        index = _.findIndex(messages, e => {
                                return e.id === conversation.message.id;
                        })

                        if (index === -1) {
                                let pushProhibited = false;

                                messages.forEach((obj) => {
                                        if (obj.hash && obj.hash.match(/gen\:/) && obj.hash == conversation.message.hash) {
                                                pushProhibited = true;
                                        }
                                });

                                if (!pushProhibited) {
                                        store.commit(chatModule.mutations.message.push, {
                                                eventId,
                                                message: conversation.message
                                        });
                                        console.log('[Check Message listener] push allowed');
                                } else {
                                        console.log('[Check Message listener] push prohibited');
                                }
                        }
                }

                index = _.findIndex(conversations, (e) => {
                        return e.chatType === conversation.chatType
                            &&
                            e.event?.id === eventId
                            &&
                            e.message.id === conversation.message.id;
                })

                if (index === -1) {
                        store.dispatch(chatModule.actions.conversations.update, {
                                tracker: '[Check Message listener] Signal received',
                                eventId,
                                conversation,
                                isNewUnreadMessage: false,
                                insertIfMissing: true
                        })
                }
        } else {
                return
        }

        setTimeout(() => {
                scrollToLastMessage(conversation.chatType);
        }, 100)
}