<template>
  <div>
    <div class="box">
      <div class="menu">
        <ul>
          <li>
            <a v-if="isDesktop" @click="open('faq')"></a>
            <a v-else @click="goTo('/page/faq')"></a>
            <span class="menu-item faq"></span>
            <span class="menu-item-label">{{ trans('click_here_tutorials') }}</span>
          </li>
          <li>
            <a @click="openChatSupport()"></a>
            <span class="menu-item send-message"></span>
            <span class="menu-item-label">{{ trans('send_direct_message') }}</span>
          </li>
          <li v-if="!isApp && userIsPro">
            <a v-if="isDesktop" @click="open('cancel')"></a>
            <a v-else @click="goTo('/page/cancel')"></a>
            <span class="menu-item cancel-pro"></span>
            <span class="menu-item-label">{{ trans('cancel_pro') }}</span>
          </li>
          <li>
            <a v-if="isDesktop" @click="open('privacy')"></a>
            <a v-else @click="goTo('/page/privacy')"></a>
            <span class="menu-item privacy"></span>
            <span class="menu-item-label">{{ trans('links.privacy') }}</span>
          </li>
          <li>
            <a v-if="isDesktop" @click="open('terms')"></a>
            <a v-else @click="goTo('/page/terms')"></a>
            <span class="menu-item terms"></span>
            <span class="menu-item-label">{{ trans('links.terms') }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import {mapState} from 'vuex'
export default {
  props: {
    open: { type: Function },
  },
  mixins: [
    require('@general/lib/mixin').default,
    require('@general/lib/mixin-chat').default
  ],
  data() {
    return {
    }
  },
  computed: {
    ...mapState({
      supportUser: 'supportUser',
      prepUser: 'prepUser'
    })
  },
  methods: {
    openChatSupport() {
      if (this.supportUser !== null) {
        this.startUserConversation(this.supportUser.id)
      }
    },
    openChatPrep() {
      if (this.prepUser !== null) {
        this.startUserConversation(this.prepUser.id)
      }
    }
  },
  async mounted() {
    const supportUserId = 100024
    if (this.supportUser === null) {
      let user = await this.$store.dispatch('loadUserInfo', supportUserId)
      this.$store.commit('setSupportUser', user)
    }
    const prepUserId = 100024
    if (this.prepUser === null) {
      let user = await this.$store.dispatch('loadUserInfo', prepUserId)
      this.$store.commit('setPrepUser', user)
    }
  }
}
</script>