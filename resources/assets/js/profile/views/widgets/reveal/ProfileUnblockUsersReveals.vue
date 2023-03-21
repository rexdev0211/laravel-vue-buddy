<template>
  <vue2-gesture :type="'swipeRight'" :call="handleGesture.bind(this, 'swipeRight')">
    <CustomReveal revealId="blocked-users" :isVisible="blockedUsersFormVisible" v-on:close-reveal-change-password="closeModal">
      <section class="modal unblock-users">
        <div class="inner">

          <div class="section-header">
            <i class="back" @click="closeModal"></i>
            <div class="title">{{ trans('Blocked') }}</div>
          </div>

          <vue-custom-scrollbar :settings="scrollBarSettings" class="section-body">
            <div class="step">
              <div class="tab-content-wrapper">
                <div class="tab-content-inner">
                  <div class="tab-content box">
                    <div class="faces">
                      <div class="face" v-for="user in profileBlockedUsers">
                        <div class="img" :style="{'background': `url(${user.photo_small}) no-repeat center / cover`}"></div>
                        <div class="details">
                          <div class="name">{{ user.name }}</div>
                          <div class="details-trash" @click="unblockUser($event, user.id)">
                            <i class="trash"></i>
                          </div>
                        </div>
                      </div>
                    </div>

                    <infinite-loading
                        ref="infiniteLoadingBlockedUsers"
                        @infinite="getBlockedUsers"
                        spinner="bubbles">
                      <span slot="no-results">
                          {{ "No blocked users" }}
                      </span>
                      <span slot="no-more">
                          {{ "No more blocked users" }}
                      </span>
                    </infinite-loading>
                  </div>
                </div>
              </div>
            </div>
          </vue-custom-scrollbar>

        </div>
      </section>
    </CustomReveal>
  </vue2-gesture>
</template>

<script>
import CustomReveal from '@buddy/views/widgets/CustomReveal.vue';
import {mapState} from "vuex";

import discoverModule from "@discover/module/store/type";

// custom scrollbar
import vueCustomScrollbar from 'vue-custom-scrollbar'
import "vue-custom-scrollbar/dist/vueScrollbar.css"

export default {
  name: "ProfileUnblockUsersReveals",
  mixins: [require('@general/lib/mixin').default],
  components: {
    CustomReveal,
    vueCustomScrollbar
  },
  data() {
    return {
        blockedUsersFormVisible: false,
        page: 0,
        limit: 20,
        scrollBarSettings: {
          suppressScrollY: false,
          suppressScrollX: true
        }
    }
  },
  computed: {
    ...mapState({
      profileBlockedUsers: state => state.profileBlockedUsers
    })
  },
  methods: {
    async getBlockedUsers(infiniteScroll) {
      const loadBlockedUsersLimit = this.limit;
      const page = this.page;

      if (this.page === 0) {
        await this.$store.dispatch('clearBlockedUsers')
      }

      let itemsCount = await this.$store.dispatch('getBlockedUsers', {
        limit: loadBlockedUsersLimit,
        page: page
      })

      if (itemsCount) {
        infiniteScroll.loaded()
      }

      if (this.profileBlockedUsers.length < loadBlockedUsersLimit * (page + 1)) {
        infiniteScroll.complete()
      }

      this.page++;
    },
    unblockUser(event, userId) {
      let callback = async () => {
        await this.$store.dispatch('unblockUser', {
          blockedUserId: userId
        })

        this.$store.dispatch(discoverModule.actions.users.reload);
        this.$store.dispatch('loadCurrentUserInfo')
      }

      let self = this
      this.$store.dispatch('showDialog', {
        mode: 'confirm',
        message: this.trans('sure_unblock_user'),
        callback: () => { self.runLoadingFunction(event.target, callback); }
      })

    },
    reset() {
      if (this.$refs.infiniteLoadingBlockedUsers) {
        this.$refs.infiniteLoadingBlockedUsers.stateChanger.reset();
      }
      this.blockedUsersFormVisible = true;
    },
    closeModal() {
      this.blockedUsersFormVisible = false;
    },
    handleGesture(str, e) {
      if (str === 'swipeRight') {
        this.closeModal();
      }
    },
  },
  activated() {
    app.$on('show-blocked-users', this.reset);
  },
  mounted() {
    app.$on('show-blocked-users', this.reset);
  }
}
</script>

<style scoped>

</style>