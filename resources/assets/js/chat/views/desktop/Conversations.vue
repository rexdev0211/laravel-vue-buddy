<template>
    <div>
        <div class="w-app">
            <div id="application-wrapper">

                <TopBarDesktop/>

                <div class="content-wrapper">
                    <vue-custom-scrollbar class="chat-list" id="js-chat-page">
                        <div class="center">
                            <div class="tabs chat-tabs">
                                <div class="tab all" @click="selectTab('all')" :class="{'active': activeConversationGroup === 'all'}"><span>{{ trans('all') }}</span></div>
                                <div class="tab unread middle" @click="selectTab('unread')" :class="{'active': activeConversationGroup === 'unread'}"><span>{{ trans('unread') }}</span></div>
                                <div class="tab favorites-tab last" @click="selectTab('favorites')" :class="{'active': activeConversationGroup === 'favorites'}"><span>{{ trans('favorites') }}</span></div>
                            </div>
                            <div class="tab-content-wrapper chat-tabs all" ref="mobileScrollTopContainer">
                                <div class="tab-content-inner">
                                    <div class="tab-content">
                                        <div class="faces" v-for="conversation in conversations">
                                            <div class="face" v-if="activeConversationGroup === 'favorites' && conversation.interlocutor.isFavorite || activeConversationGroup !== 'favorites' && conversation.chatType === 'user'"

                                                :class="{'online': conversation.interlocutor.isOnline, 'was-online': conversation.interlocutor.wasRecentlyOnline && !conversation.interlocutor.isOnline, 'favorite': conversation.interlocutor.isFavorite && !conversation.interlocutor.deleted_at, 'unread': conversation.unreadMessagesCount}">
                                                <div class="inner">
                                                    <div @click="openUserProfile(conversation, !!conversation.interlocutor.deleted_at)" 
                                                        class="img" 
                                                        :class="{'is-deleted': !!conversation.interlocutor.deleted_at}" 
                                                        :style="!conversation.interlocutor.deleted_at && {'background': `url(${conversation.interlocutor.photo_small}) no-repeat center / cover`}">
                                                    </div>
                                                    <div @click="openModal(conversation)" class="details">
                                                        <div class="row">
                                                            <div class="name">{{ conversation.interlocutor.name }}</div>
                                                            <div class="number-indicator" v-if="conversation.unreadMessagesCount">{{ conversation.unreadMessagesCount }}</div>
                                                        </div>
                                                        <div class="row">
                                                            <template v-if="!conversation.message.cancelled">
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from}"
                                                                    v-if="conversation.message.msg_type === 'text'">
                                                                    {{ conversation.message.message }}
                                                                </div>
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from, 'photo': conversation.message.msg_type === 'image'}"
                                                                    v-if="conversation.message.msg_type === 'image'">
                                                                    {{ trans('photo') }}
                                                                </div>
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from, 'video': conversation.message.msg_type === 'video'}"
                                                                    v-if="conversation.message.msg_type === 'video'">
                                                                    {{ trans('video') }}
                                                                </div>
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from, 'location': conversation.message.msg_type === 'location'}"
                                                                    v-if="conversation.message.msg_type === 'location'">
                                                                    {{ trans('location') }}
                                                                </div>
                                                            </template>
                                                            <div class="message removed" v-else>
                                                                {{ trans('removed') }}
                                                            </div>
                                                            <div class="timeago">{{ timeAgo(conversation.message.idate) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="face" v-if="conversation.chatType === 'event'"
                                                :class="{'event-chat': conversation.chatType === 'event', 'online': conversation.event.isOnline, 'was-online': conversation.event.wasRecentlyOnline && !conversation.event.isOnline, 'favorite': conversation.event.isFavorite, 'unread': conversation.unreadMessagesCount, 'event-chat-user': conversation.event.isMine}">
                                                <div class="inner">
                                                    <div @click="openUserProfile(conversation, conversation.event.isMine && conversation.interlocutor.deleted_at)"  
                                                        class="img" 
                                                        :class="{'is-deleted': conversation.event.isMine && conversation.interlocutor.deleted_at}" 
                                                        :style="!(conversation.event.isMine && conversation.interlocutor.deleted_at) && {'background': `url(${conversation.event.isMine ? conversation.interlocutor.photo_small : conversation.event.photo_small}) no-repeat center / cover`}">
                                                    </div>
                                                    <div @click="openModal(conversation)" class="details" v-if="conversation.event.isMine">
                                                        <div class="row">
                                                            <div class="name">{{ conversation.interlocutor.name }}</div>
                                                            <div class="number-indicator" v-if="conversation.unreadMessagesCount">{{ conversation.unreadMessagesCount }}</div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="event-title"><span>{{ conversation.event.title }}</span></div>
                                                            <div class="timeago">{{ timeAgo(conversation.message.idate) }}</div>
                                                        </div>
                                                        <div class="row">
                                                            <template v-if="!conversation.message.cancelled">
                                                                <div class="message"
                                                                    :class="{'reply': conversation.message.user_from === authUserId}"
                                                                    v-if="conversation.message.msg_type === 'text'">
                                                                    {{ conversation.message.message }}
                                                                </div>
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from, 'photo': conversation.message.msg_type === 'image'}"
                                                                    v-if="conversation.message.msg_type === 'image'">
                                                                    {{ trans('photo') }}
                                                                </div>
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from, 'video': conversation.message.msg_type === 'video'}"
                                                                    v-if="conversation.message.msg_type === 'video'">
                                                                    {{ trans('video') }}
                                                                </div>
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from, 'location': conversation.message.msg_type === 'location'}"
                                                                    v-if="conversation.message.msg_type === 'location'">
                                                                    {{ trans('location') }}
                                                                </div>
                                                            </template>
                                                            <div class="message removed" v-else>
                                                                {{ trans('removed') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div @click="openModal(conversation)" class="details" v-else>
                                                        <div class="row">
                                                            <div class="event-title"><span>{{ conversation.event.title }}</span></div>
                                                            <div class="number-indicator" v-if="conversation.unreadMessagesCount">{{ conversation.unreadMessagesCount }}</div>
                                                        </div>
                                                        <div class="row">
                                                            <template v-if="!conversation.message.cancelled">
                                                                <div class="message"
                                                                    :class="{'reply': conversation.message.user_from === authUserId}"
                                                                    v-if="conversation.message.msg_type === 'text'">
                                                                    {{ conversation.message.message }}
                                                                </div>
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from, 'photo': conversation.message.msg_type === 'image'}"
                                                                    v-if="conversation.message.msg_type === 'image'">
                                                                    {{ trans('photo') }}
                                                                </div>
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from, 'video': conversation.message.msg_type === 'video'}"
                                                                    v-if="conversation.message.msg_type === 'video'">
                                                                    {{ trans('video') }}
                                                                </div>
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from, 'location': conversation.message.msg_type === 'location'}"
                                                                    v-if="conversation.message.msg_type === 'location'">
                                                                    {{ trans('location') }}
                                                                </div>
                                                            </template>
                                                            <div class="message removed" v-else>
                                                                {{ trans('removed') }}
                                                            </div>
                                                            <div class="timeago">{{ timeAgo(conversation.message.idate) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="face" v-if="conversation.chatType === 'group'"
                                                :class="{'bang-chat': conversation.chatType === 'group', 'online': conversation.event.isOnline, 'was-online': conversation.event.wasRecentlyOnline && !conversation.event.isOnline, 'favorite': conversation.event.isFavorite, 'unread': conversation.unreadMessagesCount, 'bang-chat-user': conversation.event.isMine}">
                                                <div class="inner">
                                                    <div @click="openModal(conversation,'icon')" class="img" :style="{'background': `url(${conversation.event.photo_small}) no-repeat center / cover`}"></div>
                                                    <div @click="openModal(conversation)" class="details">
                                                        <div class="row">
                                                            <div class="bang-title"><span>{{ trans('events.type.bang') }}</span></div>
                                                            <div class="number-indicator" v-if="conversation.unreadMessagesCount">{{ conversation.unreadMessagesCount }}</div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="bang-date">{{ conversation.event.date | formatDate('day-date') }}</div>
                                                            <div class="timeago">{{ timeAgo(conversation.message.idate) }}</div>
                                                        </div>
                                                        <div class="row">
                                                            <template v-if="!conversation.message.cancelled">
                                                                <div class="message"
                                                                    :class="{'reply': conversation.message.user_from === authUserId}"
                                                                    v-if="conversation.message.msg_type === 'text'">
                                                                    {{ conversation.message.message }}
                                                                </div>
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from, 'photo': conversation.message.msg_type === 'image'}"
                                                                    v-if="conversation.message.msg_type === 'image'">
                                                                    {{ trans('photo') }}
                                                                </div>
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from, 'video': conversation.message.msg_type === 'video'}"
                                                                    v-if="conversation.message.msg_type === 'video'">
                                                                    {{ trans('video') }}
                                                                </div>
                                                                <div class="message"
                                                                    :class="{'reply': authUserId === conversation.message.user_from, 'location': conversation.message.msg_type === 'location'}"
                                                                    v-if="conversation.message.msg_type === 'location'">
                                                                    {{ trans('location') }}
                                                                </div>
                                                            </template>
                                                            <div class="message removed" v-else>
                                                                {{ trans('removed') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <infinite-loading
                                            ref="infiniteLoadingChat"
                                            @infinite="getConversations"
                                            force-use-infinite-wrapper="#js-chat-page"
                                            spinner="bubbles"
                                            direction="bottom">
                                            <span slot="no-results">
                                                {{ trans('no_chat_messages') }}
                                            </span>
                                            <span slot="no-more">
                                                {{ trans('no_more_chat_messages') }}
                                            </span>
                                        </infinite-loading>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </vue-custom-scrollbar>
                </div>

            </div><!--#application-wrapper-->

        </div><!--w-app-->
    </div>
</template>

<script>
    import {mapActions, mapGetters, mapState} from 'vuex';
    import chatModule from '@chat/module/store/type'
    import notificationsModule from '@notifications/module/store/type';

    import TopBarDesktop from '@buddy/views/widgets/TopBarDesktop.vue';

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    moment.locale('en', {
        relativeTime : {
            future: "in %s",
            past:   "%s ago",
            s:  "sec",
            m:  "min",
            mm: "%d min",
            h:  "1 h",
            hh: "%d h",
            d:  "day",
            dd: "%d d",
            M:  "month",
            MM: "%d m",
            y:  "year",
            yy: "%d y"
        }
    });

    export default {
        data() {
            return {
                refreshQueued: false,
                conversationsAreLoading: false,

                pageCounters: {
                    all: 0,
                    unread: 0,
                    favorites: 0,
                },

                refresh: null,
                activeConversationGroup: 'all'
            }
        },
        components: {
            TopBarDesktop,
            vueCustomScrollbar
        },
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-chat').default,
            require('@general/lib/mixin-events').default,
        ],
        methods: {
            ...mapActions([
                'signalMessagesSeen',
                'trySwitchDiscreetMode'
            ]),
            checkScrollDebounce(event){
                if (event) {
                  let currentScroll = event.target.scrollTop;
                  let scrollHeight = event.target.scrollHeight;
                  let clientHeight = event.target.clientHeight;

                  this.scroll = currentScroll

                  if (currentScroll > 5) {
                    if (this.pullToRefreshInstance) {
                      this.pullToRefreshInstance.destroy()
                    }
                  } else {
                    this.pullToRefreshInstance = this.attachPullToRefresh('#js-chat-page')
                  }

                  if (currentScroll + clientHeight < scrollHeight) {
                      this.$refs.infiniteLoadingChat.stateChanger.reset();
                  }

                }
            },
            goToTab(tab) {
                if(tab === 'chat') {
                    this.goTo(app.lastChatPage);
                } else if(tab === 'events') {
                    this.goTo('/events');
                } else if(tab === 'pro') {
                    this.goTo('/profile/pro');
                } else if(tab === 'notifications') {
                    this.goTo('/notifications');
                } else {
                    this.goTo('/discover');
                }

                if (this.tab == tab) {
                    if (app.isMobile && this.$parent.$refs && this.$parent.$refs.mobileScrollTopContainer) {
                        $(this.$parent.$refs.mobileScrollTopContainer).animate({scrollTop: 0}, 500)
                    }
                }
            },
            async getConversations(infiniteScroll) {
                if (!this.conversationsAreLoading) {
                    this.conversationsAreLoading = true

                    if (this.pageNumber === 0) {
                        this.$store.dispatch(chatModule.actions.conversations.clearGroup, this.activeConversationGroup)
                    }

                    await this.$store.dispatch(chatModule.actions.conversations.loadGroup, {
                        page: this.pageNumber,
                        group: this.activeConversationGroup
                    })

                    if (this.conversations.length) {
                        infiniteScroll.loaded()

                        this.conversationsAreLoading = false
                    }
                    if (this.conversations.length < window.LOAD_CHAT_WINDOWS_LIMIT * (this.pageNumber + 1)) {
                        infiniteScroll.complete()

                        this.conversationsAreLoading = false
                    }

                    this.pageCounters[this.activeConversationGroup]++;
                }
            },
            selectTab(tab) {
                if (tab === 'all') {
                    this.goTo('/chat');
                }
                else if (tab === 'unread') {
                    this.goTo('/chat/unread');
                }
                else if (tab === 'favorites') {
                    this.goTo('/chat/favorites');
                }
            },
            getConversationRouterLink(conversation) {
                if (conversation.chatType === 'user') {
                    return {
                        name: 'chat-user',
                        params: {
                            userToken: conversation.interlocutor.link || conversation.interlocutor.id
                        }
                    }
                } else if (conversation.chatType === 'event'){
                    return {
                        name: 'chat-event-user',
                        params: {
                            eventId: conversation.event.id,
                            userToken: conversation.interlocutor.link || conversation.interlocutor.id
                        }
                    }
                } else if (conversation.chatType === 'group'){
                    return {
                        name: 'chat-group',
                        params: {
                            eventId: conversation.event.id
                        }
                    }
                }
            },
            checkScroll() {
                if (this.$refs.mobileScrollTopContainer && this.$refs.mobileScrollTopContainer.scrollTop > 5) {
                    if (this.refresh) this.refresh.destroy()
                } else {
                    this.refresh = this.attachPullToRefresh('#js-chat-page')
                }
            },
            reset(){
                console.log('[Widget Conversations] Reset', {activeConversationGroup: this.activeConversationGroup})
                this.pageCounters[this.activeConversationGroup] = 0
                this.$store.dispatch(chatModule.actions.conversations.clearGroup, this.activeConversationGroup)
                this.$nextTick(function(){
                    if (this.$refs.infiniteLoadingChat) {
                        this.$refs.infiniteLoadingChat.stateChanger.reset()
                    }
                });
            },

            // ###############################################
            // Cache methods
            // ###############################################
            softReload(source){
                if (this.refreshQueued) {
                    this.reset()
                    this.refreshQueued = false
                }
            },
            invalidate(){
                this.refreshQueued = true
            },
            pullRefresh(){
                this.reset()
            },
            openModal(conversation, type = 'details') {
              switch (conversation.chatType) {
                case 'user':

                  return type === 'icon' ?
                      this.openUserModal(conversation.interlocutor.link || conversation.interlocutor.id)
                      :
                      this.startUserConversation(conversation.interlocutor.id)
                case 'event':
                  return type === 'icon' ?
                      conversation.event.isMine ?
                          this.openUserModal(conversation.interlocutor.link || conversation.interlocutor.id)
                          :
                          this.openEvent(conversation.event.id, conversation.event.type)
                      :
                      this.startEventConversation(conversation.interlocutor.id, conversation.event.id)
                case 'group':
                  return type === 'icon' ?
                      this.openEvent(conversation.event.id, conversation.event.type)
                      :
                      this.startGroupConversation(conversation.event.id, false)
              }
            },
            openUserProfile(conversation, isDeleted) {
                if (isDeleted) {
                    this.showErrorNotification('profile_is_deleted')
                } else {
                    this.openModal(conversation, 'icon')
                }
            }
        },
        computed: {
            ...mapGetters({
                conversationsAll: chatModule.getters.conversations.all,
                conversationsUnread: chatModule.getters.conversations.unread,
                conversationsFavorites: chatModule.getters.conversations.favorites,
            }),
            pageNumber(){
                return this.pageCounters[this.activeConversationGroup];
            },
            conversations(){
                let conversations = []
                if (this.activeConversationGroup === 'unread') {
                    conversations = this.conversationsUnread
                } else if (this.activeConversationGroup === 'favorites') {
                    conversations = this.conversationsFavorites
                } else {
                    conversations = this.conversationsAll
                }
                return conversations
            },
        },
        watch: {
            refreshQueued: {
                immediate: true,
                handler(value) {
                    console.log('[Conversations] Watcher refreshQueued', { value })
                    if (value && !this._inactive) {
                        this.reset()
                        this.refreshQueued = false
                    }
                }
            }
        },
        created(){
            /*console.log('[Conversations] Created', {
                userId: this.userId,
                eventId: this.eventId,
                conversations: this.conversations
            })*/
            if (!this.conversations.length) {
                this.reset()
            }
        },
        activated(){
            console.log('[Conversations] Activated')
            this.signalMessagesSeen()

            this.softReload('Conversations')

            if (this.activeConversationGroup === 'favorites') {
                this.reset();
            }
        },
        mounted() {
            console.log('[Conversations] Mounted', {userId: this.userId, eventId: this.eventId})

            let routeName = this.$router.currentRoute.name;
            if (routeName === 'chat') {
                this.activeConversationGroup = 'all'
            } else if (routeName === 'chat-unread') {
                this.activeConversationGroup = 'unread'
            } else if (routeName === 'chat-favorites') {
                this.activeConversationGroup = 'favorites'
            }

            this.refresh = this.attachPullToRefresh('#js-chat-page')

            let container = document.getElementById('js-chat-page');
            container.addEventListener('scroll', this.checkScrollDebounce, true);

            let v = this
            if (app.isMobile && this.$refs && this.$refs.mobileScrollTopContainer) {
                this.$refs.mobileScrollTopContainer.addEventListener('scroll', function() {
                    v.checkScroll()
                })
            }

            app.$on('invalidate-conversations', this.invalidate)
        },
        deactivated() {
            this.finishConversation();
        },
        destroyed() {
            app.$off('invalidate-conversations')
        }
    }
</script>

<style>
.chat-list {
  position:relative;
}
</style>
