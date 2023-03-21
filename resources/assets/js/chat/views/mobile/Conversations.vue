<template>
    <div class="w-root">
        <div class="w-views">
            <div id="application-wrapper" ref="mobileScrollTopContainer">
                <transition :name="'fade'" mode="out-in">
                    <header v-if="navbarVisible">
                        <div class="shadow"></div>
                        <div class="user-box" v-if="profile.id"
                            @click="toggleProfileSidebar(null)"
                            :class="{'online': !discreetModeEnabled}">
                            <div class="img" :style="{'background': `url(${defaultPhoto}) no-repeat center / cover`}"></div>
                        </div>
                        <div class="buttons">
                            <div id="visits" class="button visits" @click="goToTab('notifications')"
                                :class="{'notificated': userHasNotifications}">
                                <div class="notificated-icon" v-if="userHasNotifications"></div>
                            </div>
                        </div>
                    </header>
                </transition>

                <div class="mobileSidebarHolder positionLeft"
                     :class="{'active': sidebar.profile.visible}">
                    <div class="mobileSidebarHide" @click="toggleProfileSidebar(false)"></div>
                    <div class="mobileSidebar">
                        <ProfileMenu/>
                    </div>
                </div>

                <div class="content-wrapper">
                    <div class="chat-list">
                        <div class="center">
                            <div class="tabs chat-tabs">
                                <div class="tab all" @click="selectTab('all')" :class="{'active': activeConversationGroup === 'all'}"><span>{{ trans('all') }}</span></div>
                                <div class="tab unread middle" @click="selectTab('unread')" :class="{'active': activeConversationGroup === 'unread'}"><span>{{ trans('unread') }}</span></div>
                                <div class="tab favorites-tab last" @click="selectTab('favorites')" :class="{'active': activeConversationGroup === 'favorites'}"><span>{{ trans('favorites') }}</span></div>
                            </div>
                            <div id="js-chat-page" class="tab-content-wrapper chat-tabs all">
                                <div class="tab-content-inner">
                                    <div class="tab-content">
                                        <swipe-list
                                            ref="swipeout"
                                            class="faces"
                                            :disabled="!swipeoutEnabled"
                                            :items="conversations"
                                            :item-disabled="item => { return item.chatType === 'group' && item.event.isMine }">
                                            <template v-slot="{item, index, revealLeft, revealRight, close, revealed}">
                                                <!-- item is the corresponding object from the array -->
                                                <!-- index is clearly the index -->
                                                <!-- revealLeft is method which toggles the left side -->
                                                <!-- revealRight is method which toggles the right side -->
                                                <!-- close is method which closes an opened side -->
                                                <div class="face" v-if="item.chatType === 'user'" :class="{'is-revealed': revealed, 'online': item.interlocutor.isOnline, 'was-online': item.interlocutor.wasRecentlyOnline && !item.interlocutor.isOnline, 'favorite': item.interlocutor.isFavorite && !item.interlocutor.deleted_at, 'unread': item.unreadMessagesCount}">
                                                    <div class="inner">
                                                        <div @click="openUserProfile(item, !!item.interlocutor.deleted_at)"
                                                            class="img"
                                                            :class="{'is-deleted': !!item.interlocutor.deleted_at}" 
                                                            :style="!item.interlocutor.deleted_at && {'background': `url(${item.interlocutor.photo_small}) no-repeat center / cover`}">
                                                        </div>
                                                        <router-link :to="getChatItemRouterLink(item)" class="details">
                                                            <div class="row">
                                                                <div class="name notranslate">{{ item.interlocutor.name }}</div>
                                                                <div class="number-indicator" v-if="item.unreadMessagesCount">{{ item.unreadMessagesCount }}</div>
                                                            </div>
                                                            <div class="row">
                                                                <template v-if="!item.message.cancelled">
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from}"
                                                                        v-if="item.message.msg_type === 'text'">
                                                                        {{ item.message.message }}
                                                                    </div>
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from, 'photo': item.message.msg_type === 'image'}"
                                                                        v-if="item.message.msg_type === 'image'">
                                                                        {{ trans('photo') }}
                                                                    </div>
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from, 'video': item.message.msg_type === 'video'}"
                                                                        v-if="item.message.msg_type === 'video'">
                                                                        {{ trans('video') }}
                                                                    </div>
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from, 'location': item.message.msg_type === 'location'}"
                                                                        v-if="item.message.msg_type === 'location'">
                                                                        {{ trans('location') }}
                                                                    </div>
                                                                </template>
                                                                <div class="message removed" v-else>
                                                                    {{ trans('removed') }}
                                                                </div>
                                                                <div class="timeago">{{ timeAgo(item.message.idate) }}</div>
                                                            </div>
                                                        </router-link>
                                                    </div>
                                                </div>

                                                <div class="face" v-if="item.chatType === 'event'" :class="{'event-chat': item.chatType === 'event', 'is-revealed': revealed, 'online': item.event.isOnline, 'was-online': item.event.wasRecentlyOnline && !item.event.isOnline, 'favorite': item.event.isFavorite, 'unread': item.unreadMessagesCount, 'event-chat-user': item.event.isMine}">
                                                    <div class="inner">
                                                        <div @click="openUserProfile(item, item.event.isMine && item.interlocutor.deleted_at)"
                                                            class="img" 
                                                            :class="{'is-deleted': item.event.isMine && item.interlocutor.deleted_at}" 
                                                            :style="!(item.event.isMine && item.interlocutor.deleted_at) && {'background': `url(${item.event.isMine ? item.interlocutor.photo_small : item.event.photo_small}) no-repeat center / cover`}">
                                                        </div>
                                                        <router-link :to="getChatItemRouterLink(item)" class="details" v-if="item.event.isMine">
                                                            <div class="row">
                                                                <div class="name notranslate">{{ item.interlocutor.name }}</div>
                                                                <div class="number-indicator" v-if="item.unreadMessagesCount">{{ item.unreadMessagesCount }}</div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="event-title"><span>{{ item.event.title }}</span></div>
                                                                <div class="timeago">{{ timeAgo(item.message.idate) }}</div>
                                                            </div>
                                                            <div class="row">
                                                                <template v-if="!item.message.cancelled">
                                                                    <div class="message"
                                                                        :class="{'reply': item.message.user_from === authUserId}"
                                                                        v-if="item.message.msg_type === 'text'">
                                                                        {{ item.message.message }}
                                                                    </div>
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from, 'photo': item.message.msg_type === 'image'}"
                                                                        v-if="item.message.msg_type === 'image'">
                                                                        {{ trans('photo') }}
                                                                    </div>
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from, 'video': item.message.msg_type === 'video'}"
                                                                        v-if="item.message.msg_type === 'video'">
                                                                        {{ trans('video') }}
                                                                    </div>
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from, 'location': item.message.msg_type === 'location'}"
                                                                        v-if="item.message.msg_type === 'location'">
                                                                        {{ trans('location') }}
                                                                    </div>
                                                                </template>
                                                                <div class="message removed" v-else>
                                                                    {{ trans('removed') }}
                                                                </div>
                                                            </div>
                                                        </router-link>
                                                        <router-link :to="getChatItemRouterLink(item)" class="details" v-else>
                                                            <div class="row">
                                                                <div class="event-title"><span>{{ item.event.title }}</span></div>
                                                                <div class="number-indicator" v-if="item.unreadMessagesCount">{{ item.unreadMessagesCount }}</div>
                                                            </div>
                                                            <div class="row">
                                                                <template v-if="!item.message.cancelled">
                                                                    <div class="message"
                                                                        :class="{'reply': item.message.user_from === authUserId}"
                                                                        v-if="item.message.msg_type === 'text'">
                                                                        {{ item.message.message }}
                                                                    </div>
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from, 'photo': item.message.msg_type === 'image'}"
                                                                        v-if="item.message.msg_type === 'image'">
                                                                        {{ trans('photo') }}
                                                                    </div>
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from, 'video': item.message.msg_type === 'video'}"
                                                                        v-if="item.message.msg_type === 'video'">
                                                                        {{ trans('video') }}
                                                                    </div>
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from, 'location': item.message.msg_type === 'location'}"
                                                                        v-if="item.message.msg_type === 'location'">
                                                                        {{ trans('location') }}
                                                                    </div>
                                                                </template>
                                                                <div class="message removed" v-else>
                                                                    {{ trans('removed') }}
                                                                </div>
                                                                <div class="timeago">{{ timeAgo(item.message.idate) }}</div>
                                                            </div>
                                                        </router-link>
                                                    </div>
                                                </div>

                                                <div class="face" v-if="item.chatType === 'group'" :class="{'bang-chat': item.chatType === 'group', 'is-revealed': revealed, 'online': item.event.isOnline, 'was-online': item.event.wasRecentlyOnline && !item.event.isOnline, 'favorite': item.event.isFavorite, 'unread': item.unreadMessagesCount, 'bang-chat-user': item.event.isMine}">
                                                    <div class="inner">
                                                        <router-link :to="getChatItemRouterLink(item,'icon')" class="img" :style="{'background': `url(${item.event.photo_small}) no-repeat center / cover`}"></router-link>
                                                        <router-link :to="getChatItemRouterLink(item)" class="details">
                                                            <div class="row">
                                                                <div class="bang-title"><span>{{ item.event.type === 'club' ? item.event.title : trans('events.type.bang') }}</span></div>
                                                                <div class="number-indicator" v-if="item.unreadMessagesCount">{{ item.unreadMessagesCount }}</div>
                                                            </div>
                                                            <div class="row" v-if="item.event.type !== 'club'">
                                                                <div class="bang-date"><span>{{ item.event.date | formatDate('day-date') }}</span></div>
                                                                <div class="timeago">{{ timeAgo(item.message.idate) }}</div>
                                                            </div>
                                                            <div class="row">
                                                                <template v-if="!item.message.cancelled">
                                                                    <div class="message"
                                                                        :class="{'reply': item.message.user_from === authUserId}"
                                                                        v-if="item.message.msg_type === 'text'">
                                                                        {{ item.message.message }}
                                                                    </div>
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from, 'photo': item.message.msg_type === 'image'}"
                                                                        v-if="item.message.msg_type === 'image'">
                                                                        {{ trans('photo') }}
                                                                    </div>
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from, 'video': item.message.msg_type === 'video'}"
                                                                        v-if="item.message.msg_type === 'video'">
                                                                        {{ trans('video') }}
                                                                    </div>
                                                                    <div class="message"
                                                                        :class="{'reply': authUserId === item.message.user_from, 'location': item.message.msg_type === 'location'}"
                                                                        v-if="item.message.msg_type === 'location'">
                                                                        {{ trans('location') }}
                                                                    </div>
                                                                </template>
                                                                <div class="message removed" v-else>
                                                                    {{ trans('removed') }}
                                                                </div>
                                                                <div class="timeago" v-if="item.event.type === 'club'">{{ timeAgo(item.message.idate) }}</div>
                                                            </div>
                                                        </router-link>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- right swipe side template and v-slot:right"{ item }" is the item clearly -->
                                            <!-- remove if you dont wanna have right swipe side  -->
                                            <template v-slot:right="{ item }">
                                                <div
                                                    class="delete-message"
                                                    @click="item.chatType === 'user' ?
                                                        removeConversation(item.interlocutor.id)
                                                        :
                                                        (item.chatType === 'event'
                                                        ? removeEventConversation(item.event.id, item.interlocutor.id)
                                                        : removeGroupConversation(item.event.id))
                                                        ">
                                                    <i class="trash"></i>
                                                </div>
                                            </template>

                                            <template v-slot:empty>
                                                <div>
                                                    <!-- change mockSwipeList to an empty array to see this slot in action  -->
                                                    list is empty ( filtered or just empty )
                                                </div>
                                            </template>
                                        </swipe-list>

                                        <infinite-loading
                                            ref="infiniteLoadingChat"
                                            @infinite="getConversations"
                                            force-use-infinite-wrapper="#application-wrapper"
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
                    </div>
                </div>

                <BottomBar tab="chat"/>
            </div><!--#application-wrapper-->

        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import {mapActions, mapGetters, mapState} from 'vuex';
    import BottomBar from '@buddy/views/widgets/BottomBar.vue';
    import {SwipeList, SwipeOut} from 'vue-swipe-actions';
    import 'vue-swipe-actions/dist/vue-swipe-actions.css';
    import chatModule from '@chat/module/store/type'
    import notificationsModule from '@notifications/module/store/type';

    import ProfileMenu from "@profile/views/widgets/ProfileMenu.vue";
    import _ from "lodash";

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
                navbarVisible: true,
                conversationsAreLoading: false,
                pageCounters: {
                    all: 0,
                    unread: 0,
                    favorites: 0,
                },

                refresh: null,
                activeConversationGroup: 'all',
                swipeoutEnabled: true,
                deactivatedComponent: false,
                isDeactivated: false,
            }
        },
        components: {
            ProfileMenu,
            BottomBar,
            SwipeOut,
            SwipeList
        },
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-chat').default,
            require('@general/lib/mixin-clubs').default,
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
            setChatPosition() {
              app.prevChatPosition = this.$refs.mobileScrollTopContainer ? this.$refs.mobileScrollTopContainer.scrollTop : 0
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
                        limit: window.LOAD_CHAT_WINDOWS_LIMIT,
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
            toggleProfileSidebar(value) {
                if (value === null){
                    value = !this.sidebar.profile.visible
                }
                this.$store.dispatch('updateSidebarVisibility', {index: 'profile', value})
            },
            handleGesture(str, e) {
                if (str == 'swipeLeft') {
                    if (this.sidebar.profile.visible) {
                        this.toggleProfileSidebar(false)
                    }
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
            getChatItemRouterLink(conversation, type = 'details') {
              let entityType;
              switch (conversation.chatType) {
                case 'user':
                  return {
                    name: type === 'icon' ? 'user' : 'chat-user',
                    params: {
                      userToken: conversation.interlocutor.link || conversation.interlocutor.id
                    }
                  }
                case 'event':
                  entityType = conversation.event.type === 'bang' ? 'bang' : 'event';
                  let name = type === 'icon' ?
                      conversation.event.isMine ?
                          'user'
                          :
                          entityType
                      :'chat-event-user'
                  let params = type === 'icon' ?
                      conversation.event.isMine ?
                          {
                            userToken: conversation.interlocutor.link || conversation.interlocutor.id
                          }
                          :
                          {
                            eventId: conversation.event.id,
                          }
                      :{
                        eventId: conversation.event.id,
                        userToken: conversation.interlocutor.link || conversation.interlocutor.id
                      }
                  return {
                    name: name,
                    params: params
                  }
                case 'group':
                  entityType = conversation.event.type === 'bang' ? 'bang' : 'event';
                  return {
                    name: type === 'icon' ? entityType : 'chat-group',
                    params: {
                      eventId: conversation.event.id
                    }
                  }
              }
            },
            openUserProfile(item, isDeleted) {
                if (isDeleted) {
                    this.showErrorNotification('profile_is_deleted')
                } else {
                    this.$router.push(this.getChatItemRouterLink(item, 'icon'))
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
                console.log('[Mobile Conversations] Reset', {activeConversationGroup: this.activeConversationGroup})
                this.pageCounters[this.activeConversationGroup] = 0
                this.$store.dispatch(chatModule.actions.conversations.clearGroup, this.activeConversationGroup)
                this.$nextTick(function(){
                    if (!!this.$refs.infiniteLoadingChat){
                        this.$refs.infiniteLoadingChat.stateChanger.reset()
                    }
                })
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
        },
        computed: {
            ...mapState({
                sidebar: state => state.sidebar,
                profile: 'profile',
            }),
            ...mapGetters({
                discreetModeEnabled: 'discreetModeEnabled',
                getUser: 'getUser',
                getEvent: 'getEvent',
                conversationsAll: chatModule.getters.conversations.all,
                conversationsUnread: chatModule.getters.conversations.unread,
                conversationsFavorites: chatModule.getters.conversations.favorites,
                userHasNewMessages: 'userHasNewMessages',
                userHasNotifications: 'userHasNotifications'
            }),
            defaultPhoto() {
                let avatars = this.profile.avatars
                return _.get(avatars, 'merged.photo_small', '/assets/img/default_180x180.jpg')
            },
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

            this.isDeactivated = false

            if (this.userHasNewMessages) {
                this.signalMessagesSeen()
            }

            this.softReload('Conversations')

            let container = document.querySelector('#application-wrapper');

            if (container.scrollTop !== app.prevChatPosition) {
                container.scrollTop = app.prevChatPosition;
            }

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

            let v = this

            if (app.isMobile && this.$refs && this.$refs.mobileScrollTopContainer) {
              this.$refs.mobileScrollTopContainer.addEventListener('scroll', function() {
                if (!v.isDeactivated) {
                  v.checkScrollDebounce();
                  v.setChatPosition();
                  v.checkScroll();
                }
              })
            }

            app.$on('invalidate-conversations', this.invalidate)
        },
        deactivated() {
            this.isDeactivated = true
        },
        destroyed() {
            app.$off('invalidate-conversations')
        }
    }
</script>
<style scoped>
    header {
      position: fixed;
      z-index: 102;
    }

    .faces {
        flex-flow: column nowrap !important;
    }

    .chat-tabs {
      position: static !important;
    }

    .tab-content-inner {
      width: 100%;
      height: 100%;
    }

    .content-wrapper {
        position: relative;
        padding-top: 75px;
    }
    .details {
      text-decoration: none;
    }
</style>
