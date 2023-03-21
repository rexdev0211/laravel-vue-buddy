<template>
    <div v-if="mode === 'event' && entitiesLoaded"
        :class="{
            'w-chat__widget chat-event': true,
            'minimize': widget,
            'preview-chat': previewMode === 'chat-preview'
        }"><!--add minimize class for small chat-->
        <div class="conversation-screen">
            <vue-custom-scrollbar
                :swicher="activeScrollSidebar"
                id="conversation-event-scroll"
                class="conversation user-modal event"
                :class="{'disable-scroll': !activeScrollSidebar}"
                :settings="scrollBarSettings"
                :style="{'position': disableScroll ? 'static' : 'relative'}"
            >
                <div class="header">

                    <div class="info group"
                        :class="{'online': info.event.isOnline, 'was-online': info.event.wasRecentlyOnline && !info.event.isOnline}"
                        @click="chatProfileClick()">
                        <div class="img" 
                            :class="{'is-deleted': !!info.deleted_at}"    
                            :style="!info.deleted_at && {'background': `url(${info.photo_small}) no-repeat center / cover`}">
                        </div>
                        <div class="col">
                            <div class="event-title">{{ info.title }}</div>
                            <div class="how-far notranslate" v-if="isMyEvent(event)">{{ getDistanceString(event) }}</div>
                            <div class="date" v-else>{{ event.date | formatDate('day-date') }}</div>
                        </div>
                    </div>
                    <div class="controls">

                        <div v-if="previewMode === 'chat-preview'" class="hide-button" @click="hideChat('event', event.id, user.id); clearHiddenChat(user.id, event.id)">
                            <i class="hide-button--icon"></i>
                        </div>
                        <i class="trash" @click="removeEventConversation(event.id, user.id); finishConversation();"></i>
                        <i class="back" @click="finishConversation(); clearHiddenChat(user.id, event.id)"></i>
                    </div>
                </div>
                <ChatComponent
                    :chatMode="chatMode"
                    :userId="user.id"
                    :eventId="event.id"
                    @changeScrollBarState="changeScrollBarState"
                    @showScrollBottomButton="showScrollBottomButton"
                    @changeScrollStyle="changeScrollStyle"
                >
                </ChatComponent>

                <EventChatPhotosReveals></EventChatPhotosReveals>
                <EventChatVideosReveals></EventChatVideosReveals>
            </vue-custom-scrollbar>
            <transition name="fade" v-if="isDesktop">
              <div v-show="showScrollBottom"
                   class="scroll_bottom-button"
                   style="margin-bottom: 10px; position: absolute; right: 20px; bottom: 205px;"
                   @click="scrollToBottom()" :title="trans('arrow_scroll_top')">
                <svg class="icon icon-arrow_down"><use v-bind:xlink:href="symbolsSvgUrl('icon-arrow_down')"></use></svg>
              </div>
            </transition>
        </div>
    </div>
</template>

<script>
    import ChatComponent from '@chat/views/widgets/ChatComponent.vue';
    import EventChatPhotosReveals from '@chat/views/widgets/EventChatPhotosReveals.vue';
    import EventChatVideosReveals from '@chat/views/widgets/EventChatVideosReveals.vue';
    import auth from "@general/lib/auth";
    import {mapState} from 'vuex';
    import chatModule from "../../module/store/type";

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-chat').default,
            require('@general/lib/mixin-events').default,
        ],
        components: {
            ChatComponent,
            EventChatPhotosReveals,
            EventChatVideosReveals,
            vueCustomScrollbar
        },
        data() {
            return {
                activeScrollSidebar: true,
                showScrollBottom: false,
                chatMode: 'event',
                disableScroll: true,
                scrollBarSettings: {
                    suppressScrollY: false,
                    suppressScrollX: true
                }
            }
        },
        computed: {
            entitiesLoaded(){
                return !!(this.event && this.user)
            },
            info(){
                let info = {}
                if (this.isMyEvent(this.event)) {
                    // My event, showing interlocutor info (this.user)
                    info = {
                        photo_small: this.user.photo_small || null,
                        title: this.user.name || null,
                        user: this.user || null,
                        event: this.event || null,
                        deleted_at: this.user.deleted_at || null
                    }
                } else {
                    // Not my event, showing event info (this.event)
                    info = {
                        photo_small: this.event.photo_small || null,
                        title: this.event.title || null,
                        user: this.event.user || null,
                        event: this.event || null,
                        deleted_at: null
                    }
                }
                return info
            },
            ...mapState({
                mode: state => state.chatModule.modal.mode,
                previewMode: state => state.chatModule.modal.previewMode,
                user: state => state.chatModule.modal.user || null,
                event: state => state.chatModule.modal.event || null,
                minimized: state => state.chatModule.modal.minimized,
                widget: state => state.chatModule.modal.widget,
                blockedUsersIds: state => state.blockedUsersIds,
            })
        },
        watch: {
            blockedUsersIds: function(blockedUsersIds) {
                if (blockedUsersIds.includes(this.user?.id)) {
                    this.finishConversation()
                }
            }
        },
        methods: {
            clearHiddenChat(userId, eventId) {
                let allConv = this.$store.getters[chatModule.getters.conversations.all];

                let index = _.findIndex(allConv, function (conv) {
                    return conv.chatType === 'event' && conv.event.id === eventId && conv.interlocutor.id === userId;
                })

                let currentConv = allConv[index];
                currentConv.active = false;

                this.$set(allConv, index, currentConv);
            },
            showScrollBottomButton(value) {
                this.showScrollBottom = value;
            },
            changeScrollBarState(value) {
                this.activeScrollSidebar = value;
            },
            changeScrollStyle(activeScrollSidebar, disableScroll) {
                this.activeScrollSidebar = activeScrollSidebar;
                this.disableScroll = disableScroll;
            },
            scrollToBottom() {
                if (app.isMobile) {
                    const scrollElement = this.$el.querySelector('#js-chat-user-cmp').lastElementChild;
                    this.$nextTick(() => {
                        scrollElement.scrollIntoView({block: "center"})
                    });
                } else {
                    const containerName = this.chatMode.toLowerCase();
                    const scrollDiv = document.getElementById('conversation-event-scroll');

                    const lastMessageElem = this.$el.querySelector('#js-chat-user-cmp').lastElementChild;
                    const coordinates = scrollDiv.scrollHeight - lastMessageElem.offsetHeight;

                    this.$nextTick(() => {
                        scrollDiv.scroll({
                            top: coordinates,
                        })
                    })
                }
            },
            async chatProfileClick() {
                if (this.minimized) {
                    this.maximizeChat();
                } else {
                    if (this.isMyEvent(this.event)) {
                        this.openUserModal(this.info.user.id, 6);
                    } else {
                        this.openEvent(this.event.id, this.event.type);
                    }
                }
            }
        },
        mounted(){
            /*console.log('[ModalChatEventDesktopComponent] mounted', {
                event: this.event
            })*/
        }
    }
</script>