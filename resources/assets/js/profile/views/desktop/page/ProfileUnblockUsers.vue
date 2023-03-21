<template>
  <div class="w-root">
    <div class="w-views">
      <div class="secondary-menu-nested unblocked-menu">

        <div class="secondary-menu-header">
          <i class="back" @click="goTo('/profile/settings')"></i>
          <div class="title">{{ trans('Blocked') }}</div>
        </div>

        <div class="secondary-menu-body">
          <ProfileUnblockUsersForm></ProfileUnblockUsersForm>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mapActions, mapGetters, mapState} from 'vuex';

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
      await this.$store.dispatch('unblockUser', {
        blockedUserId: userId
      })
    }
  },
  activated() {
    this.$refs.infiniteLoadingBlockedUsers.stateChanger.reset();
  }
}
</script>

<style scoped>

</style>