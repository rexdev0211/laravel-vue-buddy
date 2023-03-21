<template>
    <div v-if="mode === 'user' && entitiesLoaded"
        :class="{
            'w-chat__widget chat-user': true,
            'minimize': widget,
            'preview-chat': previewMode === 'chat-preview'
        }"><!--add minimize class for small chat-->
        <div class="conversation-screen">
            <vue-custom-scrollbar
                :swicher="activeScrollSidebar"
                id="conversation-user-scroll"
                class="conversation user-modal user"
                :settings="scrollBarSettings"
                :class="{'disable-scroll': !activeScrollSidebar}"
                :style="{'position': disableScroll ? 'static' : 'relative'}"
            >
                <div class="header">

                    <div class="info user" @click="chatProfileClick()"
                        :class="{'online': user.isOnline, 'was-online': user.wasRecentlyOnline && !user.isOnline}">
                        <div class="img" 
                            :class="{'is-deleted': user.deleted_at}" 
                            :style="!user.deleted_at && {'background': `url(${user.photo_small}) no-repeat center / cover`}">
                        </div>
                        <div class="col">
                            <div class="name notranslate">{{ user.name }}</div>
                            <div class="how-far notranslate">{{ getDistanceString(user) }}</div>
                        </div>
                    </div>
                    <div class="controls">
                        <div v-if="previewMode === 'chat-preview'" class="hide-button" @click="hideChat('user', false, user.id); clearHiddenChat(user.id);">
                            <i class="hide-button--icon"></i>
                        </div>
                        <i class="trash" @click="removeConversation(user.id); finishConversation();"></i>
                        <i class="back" @click="finishConversation(); clearHiddenChat(user.id);"></i>
                    </div>
                </div>
                <ChatComponent
                    :chatMode="chatMode"
                    :userId="user.id"
                    @changeScrollBarState="changeScrollBarState"
                    @showScrollBottomButton="showScrollBottomButton"
                    @changeScrollStyle="changeScrollStyle"
                >
                </ChatComponent>

                <ChatPhotosReveals></ChatPhotosReveals>
                <ChatVideosReveals></ChatVideosReveals>
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
    import ChatPhotosReveals from '@chat/views/widgets/ChatPhotosReveals.vue';
    import ChatVideosReveals from '@chat/views/widgets/ChatVideosReveals.vue';
    import {mapState} from 'vuex';
    import chatModule from "../../module/store/type";

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-chat').default,

        ],
        components: {
            ChatComponent,
            ChatPhotosReveals,
            ChatVideosReveals,
            vueCustomScrollbar
        },
        data() {
            return {
                activeScrollSidebar: true,
                showScrollBottom: false,
                chatMode: 'user',
                disableScroll: true,
                scrollBarSettings: {
                    suppressScrollY: false,
                    suppressScrollX: true
                }
            }
        },
        computed: {
            entitiesLoaded(){
                return !!this.user
            },
            ...mapState({
                user: state => state.chatModule.modal.user,
                mode: state => state.chatModule.modal.mode,
                previewMode: state => state.chatModule.modal.previewMode,
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
            clearHiddenChat(userId) {
                let allConv = this.$store.getters[chatModule.getters.conversations.all];

                let index = _.findIndex(allConv, function (conv) {
                    return conv.chatType === 'user' && conv.interlocutor.id === userId;
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
                    const scrollDiv = document.getElementById(`conversation-user-scroll`);

                    const lastMessageElem = this.$el.querySelector('#js-chat-user-cmp').lastElementChild;
                    const coordinates = scrollDiv.scrollHeight - lastMessageElem.offsetHeight;

                    this.$nextTick(() => {
                      scrollDiv.scroll({
                        top: coordinates,
                      })
                    })
                }
            },
            chatProfileClick() {
                if (this.minimized) {
                    this.maximizeChat();
                } else {
                    this.openUserModal(this.user.id, 5);
                }
            }
        },
        mounted(){
            /*console.log('[ModalChatDesktopComponent] mounted', {
                user: this.user
            })*/
        }
    }
</script>