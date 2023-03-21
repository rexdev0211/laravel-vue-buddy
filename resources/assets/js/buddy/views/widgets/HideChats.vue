<template>
    <div class="hidden-chats" v-if="hiddenChats.length > 0">
      <div class="hidden-faces" v-for="(hiddenChat, index) in hiddenChats">
          <div class="hidden-face user" v-if="hiddenChat.chatType === 'user'">
            <div class="img" @click="startUserConversation(hiddenChat.interlocutor.id, true);"
                :class="{'is-deleted': !!hiddenChat.interlocutor.deleted_at}"
                :style="!hiddenChat.interlocutor.deleted_at && {'background': `url(${hiddenChat.interlocutor.photo_small}) no-repeat center / cover`}">
            </div>
            <div class="close" @click="closeChat()"></div>
          </div>
          <div class="hidden-face event" v-if="hiddenChat.chatType === 'event'">
            <div class="img" @click="startEventConversation(hiddenChat.interlocutor.id, hiddenChat.event.id);"
                :class="{'is-deleted': !!hiddenChat.interlocutor.deleted_at}"
                :style="!hiddenChat.interlocutor.deleted_at && {'background': `url(${hiddenChat.event.isMine ? hiddenChat.interlocutor.photo_small : hiddenChat.event.photo_small}) no-repeat center / cover`}">
              </div>
            <div class="close" @click="closeChat()"></div>
          </div>
          <div class="hidden-face bang" v-if="hiddenChat.chatType === 'group'">
            <div @click="startGroupConversation(hiddenChat.event.id);" class="img" :style="{'background': `url(${hiddenChat.event.photo_small}) no-repeat center / cover`}"></div>
            <div class="close" @click="closeChat()"></div>
          </div>
      </div>
    </div>
</template>

<script>
import {mapState} from "vuex";
import chatModule from "../../../chat/module/store/type";

export default {
  name: "HideChats",
  mixins: [
    require('@general/lib/mixin-chat').default,
  ],
  computed: {
    ...mapState({
        hiddenChats: state => state.chatModule.hiddenChats
    }),
  },
  methods: {
    closeChat() {
      this.$store.commit(chatModule.mutations.conversations.clearHiddenChats);
    }
  }
}
</script>

<style scoped>

</style>