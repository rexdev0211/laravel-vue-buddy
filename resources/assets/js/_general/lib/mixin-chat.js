import chatModule from '@chat/module/store/type';
import eventsModule from '@events/module/store/type';

export default {
    methods: {
        async startUserConversation(userToken, widget = false) {
            if (this.isMobile) {
                setTimeout(function() { $('#reveal-overlay-card').hide() }, 100)
                setTimeout(function() { $('footer').hide() }, 100)
                setTimeout(function() { $('.child-view').hide() }, 100)
                // this.goTo(`/chat/${userToken}`)
            }

            console.log('[startUserConversation] Launched', {userToken})
            this.$store.commit(chatModule.mutations.modal, {
                event: null,
                user: null,
                mode: null,
                minimized: false,
                widget: widget
            });

            let user = await this.$store.dispatch('loadUserInfo', userToken)
            console.log('[startUserConversation] Loaded user', {user})
            if (user) {
                this.$store.commit(chatModule.mutations.modal, { user, event: null, mode: 'user', minimized: false, widget: widget });
                $('.w-chat__widget').foundation();
                this.maximizeChat();
            }
        },

        async startGroupConversation(eventId, widget = true) {
            if (this.isMobile) {
                this.goTo(`/chat/group/${eventId}`)
                return
            }

            console.log('[startGroupConversation] Launched', {eventId})
            this.$store.commit(chatModule.mutations.modal, {
                event: null,
                user: null,
                mode: null,
                minimized: false,
            })

            let event = this.$store.dispatch(eventsModule.actions.events.loadInfo, eventId)

            let self = this
            Promise.all([event]).then(value => {
                console.log('[startEventConversation] Loaded event and user', {value})
                self.$store.commit(chatModule.mutations.modal, {
                    user: null,
                    event: value[0],
                    mode: 'group',
                    minimized: false,
                })

                $('.w-chat__widget').foundation();
                self.maximizeChat();
            }, reason => {
                console.log('[startGroupConversation] Rejected', {reason})
            });
        },

        async startEventConversation(userToken, eventId) {
            if (this.isMobile) {
                this.goTo(`/chat/event-user/${eventId}/${userToken}`)
                return
            }

            console.log('[startEventConversation] Launched', {userToken, eventId})
            this.$store.commit(chatModule.mutations.modal, {
                event: null,
                user: null,
                mode: null,
                minimized: false
            })

            //this.$store.dispatch(eventsModule.actions.setBang, { visible: false })
            //this.$store.dispatch(eventsModule.actions.setEvent, { visible: false })

            let event = this.$store.dispatch(eventsModule.actions.events.loadInfo, eventId)
            let user = this.$store.dispatch('loadUserInfo', userToken)

            let self = this
            Promise.all([user, event]).then(value => {
                console.log('[startEventConversation] Loaded event and user', {value})
                self.$store.commit(chatModule.mutations.modal, {
                    user: value[0],
                    event: value[1],
                    mode: 'event',
                    minimized: false
                })

                $('.w-chat__widget').foundation();
                self.maximizeChat();
            }, reason => {
                console.log('[startEventConversation] Rejected', {reason})
            });
        },
        removeConversation(userId) {
            let self = this
            this.$store.dispatch('showDialog', {
                mode: 'confirm',
                message: this.trans('sure_delete_conversation'),
                callback: () => {
                    axios.post('/api/removeConversation/'+userId)
                        .then(() => {
                            self.$store.dispatch(chatModule.actions.conversations.remove, {userId})
                            if (self.isMobile && app.$route.path !== '/chat') {
                                self.goTo('/chat')
                            } else {
                                self.$emit('reload-conversations')
                            }
                        })
                }
            })
        },
        removeEventConversation(eventId, userId) {
            let self = this
            this.$store.dispatch('showDialog', {
                mode: 'confirm',
                message: this.trans('sure_delete_conversation'),
                callback: () => {
                    axios.post(`/api/removeEventConversation/${eventId}/${userId}`)
                        .then(() => {
                            self.$store.dispatch(chatModule.actions.conversations.remove, {eventId, userId});
                            if (self.isMobile) {
                                self.goTo('/chat');
                            } else {
                                self.$emit('reload-conversations')
                            }
                        });
                }
            })
        },
        removeGroupConversation(eventId) {
            let self = this

            let payload = {
                eventId,
            }

            this.showDialog({
                mode: 'confirm',
                message: `Leave this club?`,
                callback: () => {
                    payload.action = 'leave'
                    self.requestMembership(payload)
                }
            })
        },
        finishConversation() {
            let self = this
            setTimeout(() => {
                self.$store.commit(chatModule.mutations.modal, { event: null, user: null, mode: null, minimized: false });

                if (this.isMobile) {
                    $('#reveal-overlay-card').show()
                    $('footer').show()
                    $('.child-view').show()

                    // setTimeout(function() { $('#reveal-overlay-card').show() }, 100)
                    // setTimeout(function() { $('footer').show() }, 100)
                }
            }, 25)
        },
        hideChat(chatType, eventId, userId) {
            let index,
                allConversations = this.$store.getters[chatModule.getters.conversations.all];

            if (chatType === 'event') {
                index = _.findIndex(allConversations, function (conversation) {
                    return conversation.chatType === 'event' && conversation.event.id === eventId && conversation.interlocutor.id === userId;
                })
            } else if (chatType === 'bang') {
                index = _.findIndex(allConversations, function (conversation) {
                    return conversation.chatType === 'group' && conversation.event.id === eventId;
                })
            } else if (chatType === 'user') {
                index = _.findIndex(allConversations, function (conversation) {
                    return conversation.chatType === 'user' && conversation.interlocutor.id === userId;
                })
            }

            if (index !== -1) {
                let currentConversation = allConversations[index];
                console.log(currentConversation);
                this.$store.commit(chatModule.mutations.conversations.hide, { currentConversation })
                this.finishConversation();
            }
        },
        async showChatPreview() {
            let path = this.$route.path;

            if (path !== '/chat') {
                this.$store.commit(chatModule.mutations.modal, {previewMode: null})

                this.$store.commit(chatModule.mutations.modal, {previewMode: 'chat-preview'})
            }
        },
        closeChatPreview() {
            this.$store.commit(chatModule.mutations.modal, {previewMode: null})
        },
        maximizeChat() {
            this.$store.commit(chatModule.mutations.modal, { minimized: false });

            if ($('#reveal-overlay-card').css('display') == 'block') {
                let zIndex = parseInt($('#reveal-overlay-card').css('z-index'))

                setTimeout(function() { $('.w-chat__widget').css('z-index', (zIndex ? zIndex : 2000) + 1) }, 100)
            }
        },
        minimizeChat() {
            this.$store.commit(chatModule.mutations.modal, { minimized: true });
        },
    }
};
