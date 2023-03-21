<template>
    <div v-if="mode === 'group' && entitiesLoaded"
        :class="{
            'w-chat__widget chat-group': true,
            'minimize': widget,
            'preview-chat': previewMode === 'chat-preview'
        }"><!--add minimize class for small chat-->
        <div class="conversation-screen">
            <vue-custom-scrollbar
                :swicher="activeScrollSidebar"
                id="conversation-group-scroll"
                class="conversation user-modal group"
                :settings="scrollBarSettings"
                :class="{'disable-scroll': !activeScrollSidebar}"
                :style="{'position': disableScroll ? 'static' : 'relative'}"
            >
                <div class="header">

                    <div class="info group"
                        :class="{'online': info.event.isOnline, 'was-online': info.event.wasRecentlyOnline && !info.event.isOnline}"
                        @click="openEvent(event.id, 'bang')">
                        <div class="img" :style="{'background': `url(${info.photo_small}) no-repeat center / cover`}"></div>
                        <div class="col">
                            <div class="event-title">{{ trans('events.type.bang') }}</div>
                            <div class="date">{{ info.event.date | formatDate('day-date') }}</div>
                        </div>
                    </div>
                    <div class="controls">
                      <div v-if="previewMode === 'chat-preview'" class="hide-button" @click="hideChat('bang', event.id, false); clearHiddenChat(event.id)">
                        <i class="hide-button--icon"></i>
                      </div>
                      <i class="back" @click="finishConversation(); clearHiddenChat(event.id)"></i>
                    </div>

                </div>
                <GroupChatComponent
                    :chatMode="chatMode"
                    :eventId="event.id"
                    @changeScrollBarState="changeScrollBarState"
                    @showScrollBottomButton="showScrollBottomButton"
                    @changeScrollStyle="changeScrollStyle"
                >
                </GroupChatComponent>

                <GroupChatPhotosReveals></GroupChatPhotosReveals>
                <GroupChatVideosReveals></GroupChatVideosReveals>
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
    import GroupChatComponent from '@chat/views/widgets/GroupChatComponent.vue';
    import GroupChatPhotosReveals from '@chat/views/widgets/GroupChatPhotosReveals.vue';
    import GroupChatVideosReveals from '@chat/views/widgets/GroupChatVideosReveals.vue';
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
            GroupChatComponent,
            GroupChatPhotosReveals,
            GroupChatVideosReveals,
            vueCustomScrollbar
        },
        data() {
            return {
                activeScrollSidebar: true,
                showScrollBottom: false,
                chatMode: 'group',
                disableScroll: true,
                scrollBarSettings: {
                    suppressScrollY: false,
                    suppressScrollX: true
                }
            }
        },
        computed: {
            entitiesLoaded(){
                return !!this.event
            },
            info: function(){
                return {
                    photo_small: this.event.photo_small || null,
                    title: this.event.title || null,
                    user: this.event.user || null,
                    event: this.event || null
                }
            },
            ...mapState({
                mode: state => state.chatModule.modal.mode,
                previewMode: state => state.chatModule.modal.previewMode,
                event: state => state.chatModule.modal.event || null,
                minimized: state => state.chatModule.modal.minimized,
                widget: state => state.chatModule.modal.widget,
                blockedUsersIds: state => state.blockedUsersIds,
            })
        },
        watch: {
            blockedUsersIds: function(blockedUsersIds) {
                if (blockedUsersIds.includes(this.event?.user_id)) {
                    this.finishConversation()
                }
            }
        },
        methods: {
            clearHiddenChat(eventId) {
                let allConv = this.$store.getters[chatModule.getters.conversations.all];

                let index = _.findIndex(allConv, function (conv) {
                  return conv.chatType === 'group' && conv.event.id === eventId;
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
                    const scrollElement = this.$el.querySelector('#js-chat-group-cmp').lastElementChild;
                    this.$nextTick(() => {
                        scrollElement.scrollIntoView({block: "center"})
                    });
                } else {
                    const containerName = this.chatMode.toLowerCase();
                    const scrollDiv = document.getElementById('conversation-group-scroll');

                    const lastMessageElem = this.$el.querySelector('#js-chat-group-cmp').lastElementChild;
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
                    this.maximizeChat()
                } else {
                    this.openEvent(this.event.id, this.event.type)
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