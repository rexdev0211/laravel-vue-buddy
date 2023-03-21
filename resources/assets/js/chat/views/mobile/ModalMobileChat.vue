<template>
    <div
        v-if="mode === 'user' && entitiesLoaded"
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
                <i class="back" @click="finishConversation(); clearHiddenChat(user.id);"></i>

                <div class="info user" @click="finishConversation(); clearHiddenChat(user.id);"
                     :class="{'online': user.isOnline, 'was-online': user.wasRecentlyOnline && !user.isOnline}">
                  <div class="img" :style="{'background': `url(${user.photo_small}) no-repeat center / cover`}"></div>
                  <div class="col">
                    <div class="name notranslate">{{ user.name }}</div>
                    <div class="how-far notranslate">{{ getDistanceString(user) }}</div>
                  </div>
                </div>

                <i class="dots" @click="menuVisible = !menuVisible"></i>
                <div class="block-report" :class="{'open': menuVisible}">
                  <div id="block-profile" class="option block"
                       v-on:click="blockUser(user.id)">
                    <span>{{ trans('block_user') }}</span>
                  </div>
                  <div id="report-profile" class="option report"
                       v-on:click="showReportMenu">
                    <span>{{ trans('report_user') }}</span>
                  </div>
                </div>
              </div>
              <div class="content-wrapper">
                <ChatComponent
                    :chatMode="chatMode"
                    :userId="user.id"
                    @changeScrollBarState="changeScrollBarState"
                    @showScrollBottomButton="showScrollBottomButton"
                    @changeScrollStyle="changeScrollStyle"
                >
                </ChatComponent>
              </div>

                <ChatPhotosReveals></ChatPhotosReveals>
                <ChatVideosReveals></ChatVideosReveals>
            </vue-custom-scrollbar>
        </div>



      <div class="mobileSidebarHolder positionRight forDesktop"
           v-show="showReportMenu"
           :class="{'active': reportMenuVisible}">
        <div class="mobileSidebarHide" @click="hideReportMenu"></div>
        <div class="mobileSidebar">

          <div class="report-menu">
            <div class="inner">
              <i class="back" @click="hideReportMenu"></i>
              <div class="title">{{ trans('report_user') }}</div>
              <div class="box">
                <div class="checkbox-container">
                  <label class="checkbox-label">
                    <input type="checkbox" name="spam" value="spam" v-model="reportData">
                    <span class="checkbox-custom"></span>
                    <div class="input-title">{{ trans('report_spam') }}</div>
                  </label>
                </div>
                <div class="checkbox-container">
                  <label class="checkbox-label">
                    <input type="checkbox" name="fake" value="fake" v-model="reportData">
                    <span class="checkbox-custom"></span>
                    <div class="input-title">{{ trans('report_fake') }}</div>
                  </label>
                </div>
                <div class="checkbox-container">
                  <label class="checkbox-label">
                    <input type="checkbox" name="harassment" value="harassment" v-model="reportData">
                    <span class="checkbox-custom"></span>
                    <div class="input-title">{{ trans('report_harassment') }}</div>
                  </label>
                </div>
                <div class="checkbox-container">
                  <label class="checkbox-label">
                    <input type="checkbox" name="under-age" value="under_age" v-model="reportData">
                    <span class="checkbox-custom"></span>
                    <div class="input-title">{{ trans('report_under_age') }}</div>
                  </label>
                </div>
                <div class="checkbox-container">
                  <label class="checkbox-label">
                    <input type="checkbox" name="other" value="other" v-model="reportData">
                    <span class="checkbox-custom"></span>
                    <div class="input-title">{{ trans('report_other') }}</div>
                  </label>
                </div>
              </div>
              <button type="send" class="btn" @click="reportUser">{{ trans('send') }}</button>
            </div>
          </div><!--report-menu-->
        </div>
      </div>


    </div>
</template>

<script>
    import ChatComponent from '@chat/views/widgets/ChatComponent.vue';
    import ChatPhotosReveals from '@chat/views/widgets/ChatPhotosReveals.vue';
    import ChatVideosReveals from '@chat/views/widgets/ChatVideosReveals.vue';
    import {mapState} from 'vuex';
    import chatModule from "@chat/module/store/type";
    import discoverModule from '@discover/module/store/type'

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
              disableScroll: false,
              scrollBarSettings: {
                  suppressScrollY: false,
                  suppressScrollX: true
              },

              menuVisible: false,
              reportMenuVisible: false,
              reportData: [],
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
            })
        },
        methods: {
          showReportMenu() {
            this.reportMenuVisible = true
          },
          hideReportMenu() {
            this.reportMenuVisible = false
          },
          hideMenu() {
            this.hideReportMenu()
            this.menuVisible = false
          },
          reportUser(userId, type) {
            axios.post(`/api/reportUser/${this.user.id}?type=${this.reportData}`)
                .then(() => {
                  this.showSuccessNotification('user_reported_confirmation');
                  this.hideMenu();
                })
          },
          async blockUser(userId) {
            this.finishConversation();
            this.closeUserModal();

            if (!this.$store.state.userIsPro && this.$store.state.blockedCount >= window.FREE_BLOCKS_LIMIT) {
              await this.$store.dispatch('requirementsAlertShow', 'blocks')
            } else {
              //close user menu
              this.hideMenu();

              // Close user profile
              if (this.user && userId == this.user.id) {
                this.closeUserModal();
              }

              // Close user chat
              if (this.chatUser && userId == this.chatUser.id) {
                this.finishConversation();
              }
              await this.blockUserById(userId)
            }
          },


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
                    const scrollDiv = document.getElementById(`conversation-${containerName}-scroll`);

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
<style>
  .disable-scroll {
      overflow-y: hidden !important;
  }
  .minimize .scroll_bottom-button {
    bottom: 105px !important;
  }
</style>
