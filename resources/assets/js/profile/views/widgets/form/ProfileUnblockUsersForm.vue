<template>
  <div class="wrap">

    <div class="tab-content-wrapper">
      <div class="tab-content-inner">
        <div class="tab-content box">
          <div class="faces">
            <div class="face" v-for="user in profileBlockedUsers">
              <div class="img" :style="{'background': `url(${user.photo_small}) no-repeat center / cover`}"></div>
              <div class="details">
                <div class="name">{{ user.name }}</div>
                <div class="details-trash" @click="unblockUser(user.id)">
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
</template>

<script>
import {mapActions, mapGetters, mapState} from 'vuex';
import chatModule from '@chat/module/store/type';
import discoverModule from "@discover/module/store/type";
import eventsModule from '@events/module/store/type';
import notificationsModule from '@notifications/module/store/type';

export default {
  name: "ProfileUnblockUsersForm",
  mixins: [require('@general/lib/mixin').default],
  data() {
    return {
      page: 0,
      limit: 20,
    }
  },
  computed: {
    ...mapState({
      profileBlockedUsers: state => state.profileBlockedUsers
    })
  },
  methods: {
    async getBlockedUsers(infiniteScroll) {
      const loadBlockedUsersLimit = 20;
      let itemsCount = await this.$store.dispatch('getBlockedUsers', {
        limit: this.limit,
        page: this.page
      })

      if (itemsCount) {
        infiniteScroll.loaded()
      }

      if (this.profileBlockedUsers.length < loadBlockedUsersLimit * (this.page + 1)) {
        infiniteScroll.complete()
      }

      this.page++;
    },
    async unblockUser(userId) {
      let callback = async () => {
        await this.$store.dispatch('unblockUser', {
          blockedUserId: userId
        })
        this.$store.commit('setHaveUnblockedUsers', true)
      }

      let self = this
      this.$store.dispatch('showDialog', {
        mode: 'confirm',
        message: this.trans('sure_unblock_user'),
        callback: () => { self.runLoadingFunction(event.target, callback); }
      })
    },
    reset() {
      this.page = 0;
      this.$store.commit('clearBlockedUsers');
      this.$refs.infiniteLoadingBlockedUsers.stateChanger.reset();
    }
  },
  activated() {
    this.reset();
  },
  mounted() {
    app.$on('show-blocked-users', this.reset);
  }
}
</script>

<style scoped>

</style>