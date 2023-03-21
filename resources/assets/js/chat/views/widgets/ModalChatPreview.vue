<template>
  <div v-if="previewMode === 'chat-preview'"
      :class="{
        'w-chat__widget w-chat__preview w-chat__modal': true,
        'minimize': minimized}"><!--add minimize class for small chat-->
    <div class="content-wrapper">
      <div class="chat-list" id="js-chat-page">
        <div class="center">
          <div class="controls">
            <span class="page-button" @click="goToMessagePage">{{ trans('open_mailbox') }}</span>
            <i class="back" @click="closeChatPreview"></i>
          </div>
          <vue-custom-scrollbar id="tab-content-wrapper-wrapper" class="tab-content-wrapper chat-tabs all" ref="mobileScrollTopContainer">
            <div class="tab-content-inner">
              <div id="tab-content-content" class="tab-content">
                <div class="faces" v-for="(conversation, index) in conversations">
                  <div class="face" v-if="conversation.chatType === 'user'"
                       :class="{
                         'online': conversation.interlocutor.isOnline,
                         'was-online': conversation.interlocutor.wasRecentlyOnline && !conversation.interlocutor.isOnline,
                         'favorite': conversation.interlocutor.isFavorite && !conversation.interlocutor.deleted_at,
                         'unread': conversation.unreadMessagesCount,
                         'active-conversation': conversation.active
                       }">
                    <div class="inner">
                      <div @click="openUserProfile(conversation, !!conversation.interlocutor.deleted_at)" 
                          class="img" 
                          :class="{'is-deleted': !!conversation.interlocutor.deleted_at}"
                          :style="!conversation.interlocutor.deleted_at && {'background': `url(${conversation.interlocutor.photo_small}) no-repeat center / cover`}">
                      </div>
                      <div @click="openModal(conversation,'details',index)" class="details">
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
                       :class="{
                         'event-chat': conversation.chatType === 'event',
                         'online': conversation.event.isOnline,
                         'was-online': conversation.event.wasRecentlyOnline && !conversation.event.isOnline,
                         'favorite': conversation.event.isFavorite,
                         'unread': conversation.unreadMessagesCount,
                         'event-chat-user': conversation.event.isMine,
                         'active-conversation': conversation.active
                       }">
                    <div class="inner">
                      <div @click="openUserProfile(conversation, conversation.event.isMine && conversation.interlocutor.deleted_at)" 
                          class="img" 
                          :class="{'is-deleted': conversation.event.isMine && conversation.interlocutor.deleted_at}"
                          :style="!(conversation.event.isMine && conversation.interlocutor.deleted_at) && {'background': `url(${conversation.event.isMine ? conversation.interlocutor.photo_small : conversation.event.photo_small}) no-repeat center / cover`}">
                      </div>
                      <div @click="openModal(conversation,'details', index)" class="details" v-if="conversation.event.isMine">
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
                      <div @click="openModal(conversation,'details',index)" class="details" v-else>
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
                       :class="{
                         'bang-chat': conversation.chatType === 'group',
                         'online': conversation.event.isOnline,
                         'was-online': conversation.event.wasRecentlyOnline && !conversation.event.isOnline,
                         'favorite': conversation.event.isFavorite,
                         'unread': conversation.unreadMessagesCount,
                         'bang-chat-user': conversation.event.isMine,
                         'active-conversation': conversation.active
                       }">
                    <div class="inner">
                      <div @click="openModal(conversation,'icon')" class="img" :style="{'background': `url(${conversation.event.photo_small}) no-repeat center / cover`}"></div>
                      <div @click="openModal(conversation,'details', index)" class="details">
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

              </div>
            </div>
            <infinite-loading
                ref="infiniteLoadingChat"
                @infinite="getConversations"
                force-use-infinite-wrapper="#tab-content-wrapper-wrapper"
                spinner="bubbles"
                direction="bottom">
                  <span slot="no-results">
                    {{ trans('no_chat_messages') }}
                  </span>
              <span slot="no-more">
                    {{ trans('no_more_chat_messages') }}
                  </span>
            </infinite-loading>
          </vue-custom-scrollbar>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Conversations from "@chat/views/mobile/Conversations";
import {mapActions, mapGetters, mapState} from 'vuex';
import BottomBar from '@buddy/views/widgets/BottomBar.vue';
import {SwipeList, SwipeOut} from 'vue-swipe-actions';
import 'vue-swipe-actions/dist/vue-swipe-actions.css';
import chatModule from '@chat/module/store/type'
import notificationsModule from '@notifications/module/store/type';

// custom scrollbar
import vueCustomScrollbar from 'vue-custom-scrollbar'
import "vue-custom-scrollbar/dist/vueScrollbar.css"

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
  name: "ModalChatPreview",
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
    }
  },
  components: {
    Conversations,
    ProfileMenu,
    BottomBar,
    SwipeOut,
    SwipeList,
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
    goToMessagePage() {
      this.closeChatPreview();
      this.goTo('/chat');
    },
    setCurrentChat(index) {
        document.querySelectorAll('.active-conversation').forEach((n) => {
          n.classList.remove('active-conversation');
        })
        let allConv = this.conversationsAll;

        allConv.forEach(function (conv) {
          conv.active = false;
        })

        let currentConv = allConv[index];

        currentConv.active = true;

        this.$set(allConv, index, currentConv);
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
    pullRefresh(){
      this.$store.dispatch(discoverModule.actions.users.setRefreshQueued, true)
      this.resetUsersAround()
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
    openModal(conversation, type = 'details', index = null) {
      switch (conversation.chatType) {
        case 'user':
          if (type === 'icon') {
            return this.openUserModal(conversation.interlocutor.link || conversation.interlocutor.id)
          }
          this.startUserConversation(conversation.interlocutor.id, true)
          return this.setCurrentChat(index)
        case 'event':
          if (type === 'icon') {
            return conversation.event.isMine ?
                this.openUserModal(conversation.interlocutor.link || conversation.interlocutor.id)
                :
                this.openEvent(conversation.event.id, conversation.event.type)
          }
          this.startEventConversation(conversation.interlocutor.id, conversation.event.id)
          return this.setCurrentChat(index);
        case 'group':
          if (type === 'icon') {
            return this.openEvent(conversation.event.id, conversation.event.type)
          }
          this.startGroupConversation(conversation.event.id);
          return this.setCurrentChat(index);
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
    ...mapState({
      chatPreview: state => state.chatModule.modal.chatPreview,
      mode: state => state.chatModule.modal.mode,
      previewMode: state => state.chatModule.modal.previewMode,
      minimized: state => state.chatModule.modal.minimized,
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
    },
    previewMode(val) {
      if (val === 'chat-preview') {
        this.signalMessagesSeen()
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

    this.softReload('Conversations')
  },
  mounted() {
    console.log('[Conversations] Mounted', {userId: this.userId, eventId: this.eventId})

    this.refresh = this.attachPullToRefresh('#js-chat-page')

    let v = this

    app.$on('invalidate-conversations', this.invalidate)
  },
  destroyed() {
    app.$off('invalidate-conversations')
  }
}
</script>

<style scoped>
.content-wrapper {
  overflow: hidden !important;
  height: 100% !important;
  padding-bottom: 0 !important;
}
.active-conversation {
  background: linear-gradient(to right,#000000 0%, #000000 -9%,transparent 97%, transparent 0%);
  border-radius: 7px;
}
</style>
